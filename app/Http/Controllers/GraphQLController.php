<?php

namespace App\Http\Controllers;

use App\GraphQL\StorefrontSchema;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * Single GraphQL endpoint for the storefront. Optional auth: the catalog is public, but
 * me/orders and every mutation need a Sanctum token — the authenticated user (or null)
 * is resolved here and handed to resolvers as $context['user']. Depth/complexity limits
 * bound the cost of a single query (DoS guard) since this route is public.
 */
class GraphQLController extends Controller
{
    private const MAX_DEPTH = 10;

    private const MAX_COMPLEXITY = 200;

    public function __invoke(Request $request, StorefrontSchema $schema): JsonResponse
    {
        $query = $request->input('query');
        if (! is_string($query) || trim($query) === '') {
            return response()->json(['errors' => [['message' => 'No GraphQL query provided.']]], 400);
        }

        $variables = $request->input('variables');
        $context = ['user' => auth('sanctum')->user()];

        $rules = array_merge(GraphQL::getStandardValidationRules(), [
            new QueryComplexity(self::MAX_COMPLEXITY),
            new QueryDepth(self::MAX_DEPTH),
        ]);

        try {
            $result = GraphQL::executeQuery(
                schema: $schema->build(),
                source: $query,
                contextValue: $context,
                variableValues: is_array($variables) ? $variables : null,
                operationName: $request->input('operationName'),
                validationRules: $rules,
            );

            $debug = config('app.debug') ? DebugFlag::INCLUDE_DEBUG_MESSAGE : DebugFlag::NONE;

            return response()->json($result->toArray($debug));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['errors' => [['message' => 'Internal server error.']]], 500);
        }
    }
}
