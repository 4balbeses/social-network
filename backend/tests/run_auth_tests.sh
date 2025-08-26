#!/bin/bash

# Auth Test Runner for Social Network Backend
# This script starts the server and runs comprehensive authentication tests

set -e

echo "🚀 Starting Social Network Backend Authentication Tests"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SERVER_HOST="localhost"
SERVER_PORT="8000"
SERVER_URL="http://$SERVER_HOST:$SERVER_PORT"
BACKEND_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "📂 Backend directory: $BACKEND_DIR"
echo "🌐 Server URL: $SERVER_URL"

# Function to check if server is running
check_server() {
    curl -s -f "$SERVER_URL" > /dev/null 2>&1
}

# Function to start the server
start_server() {
    echo -e "${YELLOW}🔧 Starting Symfony development server...${NC}"
    cd "$BACKEND_DIR"
    
    # Check if server is already running
    if check_server; then
        echo -e "${GREEN}✅ Server is already running on $SERVER_URL${NC}"
        return 0
    fi
    
    # Start server in background
    php -S "$SERVER_HOST:$SERVER_PORT" -t public/ > server.log 2>&1 &
    SERVER_PID=$!
    
    # Wait for server to start
    echo "⏳ Waiting for server to start..."
    for i in {1..30}; do
        if check_server; then
            echo -e "${GREEN}✅ Server started successfully (PID: $SERVER_PID)${NC}"
            return 0
        fi
        sleep 1
    done
    
    echo -e "${RED}❌ Failed to start server${NC}"
    return 1
}

# Function to stop the server
stop_server() {
    if [ ! -z "$SERVER_PID" ] && kill -0 "$SERVER_PID" 2>/dev/null; then
        echo -e "${YELLOW}🛑 Stopping server (PID: $SERVER_PID)...${NC}"
        kill "$SERVER_PID"
        wait "$SERVER_PID" 2>/dev/null
        echo -e "${GREEN}✅ Server stopped${NC}"
    fi
}

# Function to run database migrations
setup_database() {
    echo -e "${YELLOW}🗄️ Setting up database...${NC}"
    cd "$BACKEND_DIR"
    
    # Check if doctrine is available
    if [ -f "bin/console" ]; then
        echo "Running database migrations..."
        php bin/console doctrine:database:create --if-not-exists --env=dev || true
        php bin/console doctrine:migrations:migrate --no-interaction --env=dev || true
        echo -e "${GREEN}✅ Database setup complete${NC}"
    else
        echo -e "${YELLOW}⚠️ No console command found, skipping database setup${NC}"
    fi
}

# Function to run the authentication tests
run_auth_tests() {
    echo -e "${YELLOW}🧪 Running authentication tests...${NC}"
    cd "$BACKEND_DIR"
    
    # Run the PHP test script
    php tests/auth_test.php
}

# Function to cleanup
cleanup() {
    echo -e "\n${YELLOW}🧹 Cleaning up...${NC}"
    stop_server
    if [ -f "$BACKEND_DIR/server.log" ]; then
        rm "$BACKEND_DIR/server.log"
    fi
}

# Trap to ensure cleanup on exit
trap cleanup EXIT

# Main execution
main() {
    echo "1️⃣ Setting up environment..."
    setup_database
    
    echo -e "\n2️⃣ Starting server..."
    if ! start_server; then
        echo -e "${RED}❌ Failed to start server. Exiting.${NC}"
        exit 1
    fi
    
    echo -e "\n3️⃣ Running authentication tests..."
    run_auth_tests
    
    echo -e "\n${GREEN}🎉 All tests completed!${NC}"
}

# Run main function
main