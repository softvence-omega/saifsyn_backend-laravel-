#!/bin/bash

echo "🚀 Setting up Laravel Docker Environment..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Check if .env.docker exists
if [ ! -f .env.docker ]; then
    echo "📝 Creating .env.docker file..."
    cat > .env.docker << 'EOF'
# Docker Environment Variables
# These variables are used by docker-compose.yml

# MySQL Configuration
MYSQL_DATABASE=saifsyn
MYSQL_ROOT_PASSWORD=root_password
MYSQL_USER=laravel
MYSQL_PASSWORD=laravel_password
EOF
    echo "✅ .env.docker file created"
else
    echo "✅ .env.docker file already exists"
fi

echo ""
echo "🐳 Building and starting Docker containers..."
docker-compose up -d --build

echo ""
echo "⏳ Waiting for containers to be ready..."
sleep 10

echo ""
echo "✅ Setup complete!"
echo ""
echo "📌 Your application is running at: http://localhost:8000"
echo "📌 MySQL is accessible at: localhost:3307"
echo ""
echo "Useful commands:"
echo "  - View logs: docker-compose logs -f"
echo "  - Run migrations: docker-compose exec app php artisan migrate"
echo "  - Access shell: docker-compose exec app bash"
echo "  - Stop containers: docker-compose down"
