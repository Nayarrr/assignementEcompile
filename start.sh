#!/bin/bash

echo "🚀 Starting Cleaner Booking System..."
echo ""

# Check if we're in the right directory
if [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Start backend
echo "📦 Starting Laravel backend on port 4000..."
cd backend
npm run serve &
BACKEND_PID=$!
cd ..

# Wait a bit for backend to start
sleep 2

# Start frontend
echo "🎨 Starting Vue frontend on port 4200..."
cd frontend
npm run dev &
FRONTEND_PID=$!
cd ..

echo ""
echo "✅ Both servers started!"
echo ""
echo "📍 Backend API:  http://localhost:4000/api"
echo "🌐 Frontend App: http://localhost:4200"
echo ""
echo "👤 Admin login:"
echo "   Email:    admin@example.com"
echo "   Password: password123"
echo ""
echo "👤 User login:"
echo "   Email:    user@example.com"
echo "   Password: password123"
echo ""
echo "Press Ctrl+C to stop both servers"
echo ""

# Wait for Ctrl+C
trap "echo ''; echo '🛑 Stopping servers...'; kill $BACKEND_PID $FRONTEND_PID; exit" INT
wait
