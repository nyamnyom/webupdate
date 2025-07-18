<?php?>
<h2>Login</h2>

@if($errors->any())
    <p style="color:red">{{ $errors->first() }}</p>
@endif

<form method="POST" action="/login">
    @csrf
    <label>Username</label>
    <input type="text" name="username" required><br>

    <label>Password</label>
    <input type="password" name="password" required><br>

    <button type="submit">Login</button>
</form>
