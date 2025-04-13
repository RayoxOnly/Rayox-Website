<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rayox.site</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

body {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    flex-direction: column;
}

.container {
    text-align: center;
}

.logo {
    font-size: 5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 2rem;
    letter-spacing: -1px;
}

.buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.8rem 2rem;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.btn-login {
    background-color: #fff;
    color: #4f46e5;
    border: 2px solid #4f46e5;
    text-decoration: none;
}

.btn-login:hover {
    background-color: #f0f5ff;
}

.btn-register {
    background-color: #4f46e5;
    color: white;
    text-decoration: none;
}

.btn-register:hover {
    background-color: #4338ca;
}

a {
    text-decoration: none;
}
</style>
</head>
<body>
<div class="container">
<div class="logo">Rayox.site</div>
<div class="buttons">
<a href="/login" class="btn btn-login">Login</a>
<a href="/register" class="btn btn-register">Register</a>
</div>
</div>
</body>
</html>
