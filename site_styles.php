<?php
// Shared CSS used across the public and authenticated pages.
// This keeps the common styling in one place and reduces repeated inline CSS blocks.
?>
<style>
    :root {
        --primary: #7f6db1;
        --primary-dark: #685a95;
        --primary-soft: rgba(127, 109, 177, 0.12);
        --text: #111111;
        --bg: #f4f4f4;
        --white: #ffffff;
        --nav-black: #111111;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
    }

    .topbar {
        background: var(--nav-black);
        color: var(--white);
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
        color: var(--white);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .brand img {
        display: block;
        height: 90px;
        width: auto;
    }

    .nav {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .nav a {
        color: var(--white);
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: bold;
    }

    .nav a:hover {
        background: rgba(255,255,255,0.12);
    }
</style>
