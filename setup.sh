#!/usr/bin/env bash
# Setup script for the Liberu Ecommerce project.
#
# Supports: Standalone, Laravel Sail (Docker), Docker Compose, and Kubernetes deployments.
# Handles PHP 8.5+ detection with fallback to system php.

set -e

RED='\e[91m'
GREEN='\e[92m'
YELLOW='\e[93m'
BLUE='\e[94m'
RESET='\e[39m'

print_message() { echo -e "${1}${2}${RESET}"; }
print_header()  { echo ""; echo "=================================="; echo "$1"; echo "=================================="; echo ""; }
print_error()   { print_message "$RED" "ERROR: $1"; }
print_success() { print_message "$GREEN" "$1"; }
print_info()    { print_message "$BLUE" "$1"; }
print_warning() { print_message "$YELLOW" "WARNING: $1"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

# Detect best available PHP binary (prefer 8.5)
detect_php() {
    for php_bin in php85 php8.5 php84 php8.4 php; do
        if command_exists "$php_bin"; then
            PHP_CMD="$php_bin"
            PHP_VERSION=$($php_bin -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "unknown")
            print_info "Using PHP binary: $php_bin ($PHP_VERSION)"
            return 0
        fi
    done
    print_error "No PHP binary found. Please install PHP 8.5+."
    return 1
}

# Detect or download composer
detect_composer() {
    if command_exists composer; then
        COMPOSER_CMD="composer"
        return 0
    fi
    for loc in /usr/local/bin/composer /usr/bin/composer "$HOME/.composer/composer.phar" "$HOME/.local/bin/composer"; do
        if [ -f "$loc" ]; then
            COMPOSER_CMD="$PHP_CMD $loc"
            return 0
        fi
    done

    print_warning "Composer not found. Downloading..."
    if ! command_exists curl; then
        print_error "curl is required to download Composer. Install curl or composer manually."
        return 1
    fi
    $PHP_CMD -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    $PHP_CMD composer-setup.php --quiet
    $PHP_CMD -r "unlink('composer-setup.php');"
    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="$PHP_CMD composer.phar"
        print_success "Composer downloaded successfully."
        return 0
    fi
    print_error "Failed to download Composer."
    return 1
}

install_composer_dependencies() {
    print_header "COMPOSER INSTALL"
    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        read -p "vendor/ already exists. Reinstall? (y/n) " -n 1 -r; echo
        [[ $REPLY =~ ^[Yy]$ ]] || { print_success "Skipping composer install."; return 0; }
    fi
    detect_php || return 1
    detect_composer || return 1
    print_info "Running: $COMPOSER_CMD install"
    if eval "$COMPOSER_CMD install --no-interaction --prefer-dist --ignore-platform-req=ext-posix"; then
        print_success "Composer dependencies installed successfully."
    else
        print_error "Composer install failed."
        return 1
    fi
}

install_npm_dependencies() {
    print_header "NPM INSTALL"
    if [ -d "node_modules" ]; then
        read -p "node_modules/ already exists. Reinstall? (y/n) " -n 1 -r; echo
        [[ $REPLY =~ ^[Yy]$ ]] || { print_success "Skipping npm install."; return 0; }
    fi
    if ! command_exists npm; then
        print_error "npm not found. Install Node.js from https://nodejs.org/"
        return 1
    fi
    if npm install; then
        print_success "NPM dependencies installed successfully."
    else
        print_error "NPM install failed."
        return 1
    fi
}

build_frontend_assets() {
    print_header "NPM BUILD"
    if ! command_exists npm; then
        print_error "npm not found. Cannot build assets."
        return 1
    fi
    if npm run build; then
        print_success "Frontend assets built successfully."
    else
        print_error "NPM build failed."
        return 1
    fi
}

# Standalone installation
install_standalone() {
    print_header "STANDALONE INSTALLATION"
    detect_php || exit 1

    echo "PHP: $PHP_VERSION | User: $(whoami)"
    echo ""

    if [ ! -f ".env" ]; then
        read -p "Copy .env.example to .env? (y/n) " -n 1 -r; echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            cp .env.example .env
            print_success ".env created from .env.example"
        fi
    fi

    read -p "Database credentials configured in .env? (y/n) " -n 1 -r; echo
    [[ $REPLY =~ ^[Yy]$ ]] || { print_warning "Configure .env with database credentials and re-run."; exit 0; }

    install_composer_dependencies || exit 1
    install_npm_dependencies || print_warning "NPM install failed, continuing..."
    build_frontend_assets || print_warning "NPM build failed, continuing..."

    print_header "KEY:GENERATE"
    if $PHP_CMD artisan key:generate; then
        print_success "Application key generated."
    else
        print_error "Key generation failed."; exit 1
    fi

    print_header "MIGRATE:FRESH"
    if $PHP_CMD artisan migrate:fresh; then
        print_success "Database migrated successfully."
    else
        print_error "Migration failed."; exit 1
    fi

    print_header "DB:SEED"
    if $PHP_CMD artisan db:seed; then
        print_success "Database seeded successfully."
    else
        print_error "Seeding failed."; exit 1
    fi

    print_header "RUNNING TESTS"
    if [ -f "vendor/bin/pest" ]; then
        $PHP_CMD vendor/bin/pest --no-coverage || print_warning "Tests failed — review output above."
    elif [ -f "vendor/bin/phpunit" ]; then
        $PHP_CMD vendor/bin/phpunit --no-coverage || print_warning "Tests failed — review output above."
    else
        print_warning "No test runner found, skipping tests."
    fi

    print_header "OPTIMIZE"
    $PHP_CMD artisan optimize:clear
    $PHP_CMD artisan route:clear

    echo ""
    print_success "=================================="
    print_success "     INSTALLATION COMPLETE        "
    print_success "=================================="
    echo ""
    echo "Useful commands:"
    echo "  $PHP_CMD artisan serve          - Start development server"
    echo "  $PHP_CMD artisan horizon        - Start queue worker dashboard"
    echo "  $PHP_CMD artisan reverb:start   - Start WebSocket server"
    echo "  $PHP_CMD artisan octane:start   - Start Octane server"
    echo "  npm run dev                     - Start Vite dev server"
    echo ""
    read -p "Start development server now? (y/n) " -n 1 -r; echo
    [[ $REPLY =~ ^[Yy]$ ]] && $PHP_CMD artisan serve || print_info "Run: $PHP_CMD artisan serve"
}

# Laravel Sail (Docker) installation
install_sail() {
    print_header "LARAVEL SAIL INSTALLATION"
    print_info "Laravel Sail provides a Docker-based development environment."
    print_info "Documentation: https://laravel.com/docs/sail"

    if ! command_exists docker; then
        print_error "Docker not installed. Visit: https://docs.docker.com/get-docker/"
        exit 1
    fi

    detect_php || exit 1
    detect_composer || exit 1

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_info "Configure .env for Sail (DB_HOST=mysql, REDIS_HOST=redis, etc.)"
        read -p "Press Enter after editing .env..."
    fi

    eval "$COMPOSER_CMD install --no-interaction --ignore-platform-req=ext-posix"

    if ! grep -q '"laravel/sail"' composer.json 2>/dev/null; then
        print_warning "Laravel Sail not in composer.json. Install with: composer require laravel/sail --dev"
    fi

    SAIL_CMD="./vendor/bin/sail"
    command_exists sail && SAIL_CMD="sail"

    print_info "Starting Sail..."
    $SAIL_CMD up -d

    print_info "Running Sail setup commands..."
    $SAIL_CMD artisan key:generate
    $SAIL_CMD artisan migrate
    $SAIL_CMD artisan db:seed

    print_success "Sail installation complete! App at: http://localhost"
    print_info "Use '$SAIL_CMD' prefix for artisan commands."
    print_info "Horizon:  $SAIL_CMD artisan horizon"
    print_info "Reverb:   $SAIL_CMD artisan reverb:start"
}

# Docker Compose installation
install_docker() {
    print_header "DOCKER COMPOSE INSTALLATION"

    if ! command_exists docker; then
        print_error "Docker not installed. Visit: https://docs.docker.com/get-docker/"
        exit 1
    fi

    if ! command_exists docker-compose && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose not available. Visit: https://docs.docker.com/compose/install/"
        exit 1
    fi

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_warning "Edit .env for your environment then re-run."
        exit 0
    fi

    if command_exists docker-compose; then
        docker-compose up -d --build
    else
        docker compose up -d --build
    fi

    print_success "Containers started. App at: http://localhost:8000"
    print_info "Horizon:  docker compose --profile horizon up -d"
    print_info "Reverb:   docker compose --profile reverb up -d"
    print_info "Worker:   docker compose --profile worker up -d"
}

# Kubernetes installation
install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"

    if ! command_exists kubectl; then
        print_error "kubectl not installed. Visit: https://kubernetes.io/docs/tasks/tools/"
        exit 1
    fi

    if [ ! -d "k8s" ]; then
        print_error "k8s/ directory not found."
        exit 1
    fi

    print_info "Current context: $(kubectl config current-context)"
    read -p "Apply to this context? (y/n) " -n 1 -r; echo
    [[ $REPLY =~ ^[Yy]$ ]] || { print_info "Cancelled."; exit 0; }

    print_warning "Ensure k8s/secret.yaml contains real credentials before applying!"
    read -p "Secrets configured? (y/n) " -n 1 -r; echo
    [[ $REPLY =~ ^[Yy]$ ]] || { print_warning "Configure secrets and re-run."; exit 0; }

    if command_exists kustomize || kubectl kustomize --help >/dev/null 2>&1; then
        print_info "Applying via kustomize..."
        kubectl apply -k k8s/
    else
        print_info "Applying manifests individually..."
        kubectl apply -f k8s/namespace.yaml
        kubectl apply -f k8s/configmap.yaml
        kubectl apply -f k8s/secret.yaml
        kubectl apply -f k8s/pvc.yaml
        kubectl apply -f k8s/deployment.yaml
        kubectl apply -f k8s/service.yaml
        kubectl apply -f k8s/ingress.yaml
        kubectl apply -f k8s/hpa.yaml
        kubectl apply -f k8s/network-policy.yaml
        kubectl apply -f k8s/resource-quota.yaml
    fi

    print_success "Kubernetes resources applied!"
    print_info "Check status: kubectl get pods -n ecommerce-laravel"
    print_info "Compatible with: https://github.com/liberu-control-panel/control-panel-laravel"
}

main() {
    clear
    print_header "LIBERU ECOMMERCE - INSTALLER"
    echo "Select installation type:"
    echo ""
    echo "  1) Standalone     - Local PHP/Node.js setup"
    echo "  2) Laravel Sail   - Docker dev environment (recommended)"
    echo "  3) Docker Compose - Production container deployment"
    echo "  4) Kubernetes     - K8s cluster deployment"
    echo "  5) Exit"
    echo ""

    while true; do
        read -p "Choice (1-5): " choice
        case $choice in
            1) install_standalone; break ;;
            2) install_sail; break ;;
            3) install_docker; break ;;
            4) install_kubernetes; break ;;
            5) print_info "Cancelled."; exit 0 ;;
            *) print_warning "Please enter 1-5." ;;
        esac
    done
}

main
