#!/usr/bin/env bash
# Deploy ecommerce-laravel to a Kubernetes cluster.
# Compatible with: https://github.com/liberu-control-panel/control-panel-laravel

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OVERLAY="${1:-}"
NAMESPACE="ecommerce-laravel"

RED='\e[91m'; GREEN='\e[92m'; YELLOW='\e[93m'; BLUE='\e[94m'; RESET='\e[39m'
info()    { echo -e "${BLUE}[INFO]${RESET} $*"; }
success() { echo -e "${GREEN}[OK]${RESET} $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET} $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*"; exit 1; }

command_exists() { command -v "$1" >/dev/null 2>&1; }
command_exists kubectl || error "kubectl not found. Install from: https://kubernetes.io/docs/tasks/tools/"

CONTEXT=$(kubectl config current-context 2>/dev/null || echo "unknown")
info "Kubernetes context: ${CONTEXT}"
info "Target namespace:   ${NAMESPACE}"

if [[ -n "$OVERLAY" ]]; then
    MANIFEST_PATH="${SCRIPT_DIR}/overlays/${OVERLAY}"
    [[ -d "$MANIFEST_PATH" ]] || error "Overlay not found: ${MANIFEST_PATH}"
    info "Overlay: ${OVERLAY}"
else
    MANIFEST_PATH="${SCRIPT_DIR}"
    info "Overlay: base (no overlay)"
fi

echo ""
read -rp "Deploy to context '${CONTEXT}'? [y/N] " confirm
[[ "$confirm" =~ ^[Yy]$ ]] || { info "Cancelled."; exit 0; }

# Validate secrets are set
SECRET_FILE="${SCRIPT_DIR}/secret.yaml"
if [[ -f "$SECRET_FILE" ]]; then
    if grep -q 'value: ""' "$SECRET_FILE" 2>/dev/null; then
        warn "secret.yaml contains empty values. Configure secrets before deploying to production."
        read -rp "Continue anyway? [y/N] " confirm2
        [[ "$confirm2" =~ ^[Yy]$ ]] || { info "Cancelled."; exit 0; }
    fi
fi

# Apply manifests
if kubectl kustomize --help >/dev/null 2>&1; then
    info "Applying with kustomize..."
    kubectl apply -k "${MANIFEST_PATH}"
else
    info "Applying manifests individually..."
    for manifest in namespace.yaml configmap.yaml secret.yaml pvc.yaml \
                    mysql-statefulset.yaml redis.yaml deployment.yaml \
                    service.yaml ingress.yaml hpa.yaml \
                    network-policy.yaml resource-quota.yaml; do
        f="${SCRIPT_DIR}/${manifest}"
        [[ -f "$f" ]] && kubectl apply -f "$f"
    done
fi

echo ""
success "Resources applied."
info "Waiting for rollout..."
kubectl rollout status deployment/ecommerce-laravel-app -n "${NAMESPACE}" --timeout=120s || warn "Rollout timeout — check: kubectl get pods -n ${NAMESPACE}"

echo ""
info "Status:"
kubectl get pods -n "${NAMESPACE}"
echo ""
success "Deploy complete. Context: ${CONTEXT}"
