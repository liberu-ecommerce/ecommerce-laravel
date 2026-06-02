#!/usr/bin/env bash
# Validate Kubernetes manifests before deployment.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OVERLAY="${1:-}"
PASS=0; FAIL=0

RED='\e[91m'; GREEN='\e[92m'; YELLOW='\e[93m'; RESET='\e[39m'
ok()   { echo -e "${GREEN}[PASS]${RESET} $*"; ((PASS++)); }
fail() { echo -e "${RED}[FAIL]${RESET} $*"; ((FAIL++)); }
info() { echo -e "${YELLOW}[INFO]${RESET} $*"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

echo "=== Validating Kubernetes manifests ==="
echo ""

# Check required files exist
REQUIRED_FILES=(namespace.yaml configmap.yaml secret.yaml pvc.yaml deployment.yaml service.yaml ingress.yaml hpa.yaml network-policy.yaml resource-quota.yaml)
for f in "${REQUIRED_FILES[@]}"; do
    if [[ -f "${SCRIPT_DIR}/${f}" ]]; then
        ok "Found ${f}"
    else
        fail "Missing ${f}"
    fi
fi

# Check overlay if specified
if [[ -n "$OVERLAY" ]]; then
    OVERLAY_PATH="${SCRIPT_DIR}/overlays/${OVERLAY}"
    if [[ -d "$OVERLAY_PATH" ]]; then
        ok "Overlay '${OVERLAY}' exists"
    else
        fail "Overlay '${OVERLAY}' not found at ${OVERLAY_PATH}"
    fi
fi

# Check kustomization resources match files
if [[ -f "${SCRIPT_DIR}/kustomization.yaml" ]]; then
    ok "kustomization.yaml found"
    if command_exists kubectl && kubectl kustomize --help >/dev/null 2>&1; then
        if kubectl kustomize "${SCRIPT_DIR}" >/dev/null 2>&1; then
            ok "kustomize build succeeded"
        else
            fail "kustomize build failed"
        fi
    else
        info "kubectl kustomize not available — skipping dry-run"
    fi
fi

# Validate YAML syntax with kubectl --dry-run if available
if command_exists kubectl; then
    if kubectl apply --dry-run=client -f "${SCRIPT_DIR}/namespace.yaml" >/dev/null 2>&1; then
        ok "kubectl dry-run passed"
    else
        fail "kubectl dry-run failed (check YAML syntax)"
    fi
fi

# Check secret has required keys
SECRET_FILE="${SCRIPT_DIR}/secret.yaml"
if [[ -f "$SECRET_FILE" ]]; then
    REQUIRED_KEYS=(APP_KEY DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD REDIS_HOST)
    for key in "${REQUIRED_KEYS[@]}"; do
        if grep -q "${key}:" "$SECRET_FILE"; then
            ok "Secret key: ${key}"
        else
            fail "Missing secret key: ${key}"
        fi
    done
    if grep -q 'value: ""' "$SECRET_FILE"; then
        info "secret.yaml has empty values — fill before production deploy"
    fi
fi

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ $FAIL -eq 0 ]] && exit 0 || exit 1
