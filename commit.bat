@echo off
echo Creating commit for consultation site updates...

git add .

git commit -m "Major update: WebRTC implementation and database standardization

- Database: Standardized all connections to use MySQL Database class
- Removed SQLite references and duplicate database implementations
- Fixed configuration constants to prevent redefinition warnings
- Added guards for DB constants in config.php
- Modified database.php to suppress headers in CLI environment

- WebRTC: Implemented peer-to-peer video calling with WebSocket signaling
- Added Ratchet WebSocket server for real-time communication
- Created room-based messaging and authentication system
- Fixed variable scoping in server.php for proper message routing
- Updated video-call.js for correct sender name display

- Security: Enhanced CORS restrictions in API endpoints
- Added origin validation in WebSocket server
- Implemented shared authentication token validation

- AI: Migrated from OpenAI to Gemini for wellness assistant
- Updated documentation to reflect all changes"

echo Commit created successfully!
echo.
echo To push changes to remote repository, run: git push
"