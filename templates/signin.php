<!DOCTYPE html>
<html>
   <head>
      <link rel="stylesheet" href="/public/css/signin.css">
      <div id="fullscreen_bg" class="fullscreen_bg"/>
      <title>Twiddit</title>
       <!-- Latest compiled and minified CSS -->
      <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
      <!-- jQuery library -->
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
      <!-- Latest compiled JavaScript -->
      <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
   </head>
   <body>
      <div class="container">

	      <form class="form-signin" method="post" action="index.php">
		      <h1 class="form-signin-heading text-muted">Twiddit</h1>
		      <input id="username" name="username" type="text" class="form-control" placeholder="Username" required="" autofocus="">
		      <input id="password" name="password" type="password" class="form-control" placeholder="Password" required="">
		      <button id="logIn" class="btn btn-lg btn-primary btn-block" formmethod="post" type="submit" formaction="login">Log In</button>
		      <button id="signup" class="btn btn-lg btn-primary btn-block" type="submit" formmethod="post" formaction="signup">Sign Up</button>
	      </form>
      <?php if (isset($failure)): ?>
         <p class='bg-danger'>Invalid Username or Password</p>
      <?php endif; ?>
      <?php if (isset($signupfail)): ?>
         <p class='bg-danger'>User already exists</p>
      <?php endif; ?>
      <?php if (isset($signupsuccess)): ?>
         <p class='bg-success'>Account Created</p>
      <?php endif; ?>
      </div>
   </body>
</html>
