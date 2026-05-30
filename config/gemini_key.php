<?php
// Model default untuk fitur scan struk.
define('GEMINI_MODEL', 'gemini-2.5-flash');

// API Key dibaca dari environment variable Railway (lebih aman dari hardcode).
// Set variable GEMINI_API_KEY di Railway Dashboard > Variables.
return getenv('GEMINI_API_KEY') ?: '';
