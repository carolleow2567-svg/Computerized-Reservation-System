<?php
// ---- M2 (Train Search & Seat Selection) settings ----
const MAX_PASSENGERS   = 6;    // passengers per booking
const MAX_ADVANCE_DAYS = 60;   // how far ahead tickets can be booked
const HOLD_MINUTES     = 10;   // how long selected seats are held

// Links to other members' pages (change when file names are agreed)
const URL_LOGIN   = 'login.php';               // M1
const URL_HOME    = 'index.php';
const URL_HISTORY = 'booking-history.php';     // M4
const URL_HELP    = 'help.php';                // M5
const URL_PROFILE = 'profile.php';             // M5
const URL_NEXT    = 'passenger-details.php';   // M3 (next step after seat selection)

// Development switch.
// true  = if nobody is logged in, auto-login as the demo passenger m2demo@example.com
// false = real mode: send visitors to M1's login page. SET TO FALSE BEFORE FINAL INTEGRATION.
const DEV_LOGIN = true;
const DEV_EMAIL = 'm2demo@example.com';
