<?php
// Shared CSS used across the public and authenticated pages.
// This keeps the common styling in one place and reduces repeated inline CSS blocks.
?>
<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f4f4f4;
        color: #111111;
    }

    .topbar {
        background: #111111;
        color: white;
        padding: 16px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .brand {
        font-size: 24px;
        font-weight: bold;
    }

    .brand a {
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .brand img {
        display: block;
        height: 42px;
        width: auto;
    }

    .nav {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .nav a {
        color: white;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: bold;
    }

    .nav a:hover {
        background: rgba(255,255,255,0.12);
    }
</style>
