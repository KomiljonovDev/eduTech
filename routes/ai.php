<?php

use App\Mcp\Servers\EduServer;
use Laravel\Mcp\Facades\Mcp;

// Web server - HTTP orqali kirish mumkin
Mcp::web('/mcp/edu', EduServer::class);

// Local server - CLI orqali kirish uchun (Claude Code, etc.)
Mcp::local('edu', EduServer::class);
