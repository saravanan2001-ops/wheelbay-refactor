<?php
// Start a session to manage user login status
session_start();

// Include the database connection file
require_once 'php/db_conn.php'; // Adjust path if necessary

// Initialize variables for messages and form data
$errorMessage = '';
$successMessage = ''; // To display message if redirected after successful registration

// Check if there's a success message from registration redirect
if (isset($_SESSION['registration_success'])) {
    $successMessage = $_SESSION['registration_success'];
    $prefillEmail = $_SESSION['registered_email'] ?? '';
    unset($_SESSION['registration_success']);
    unset($_SESSION['registered_email']);
} else {
    $prefillEmail = '';
}

// 2. Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and Sanitize Input Data
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
    $password = $_POST['password'] ?? '';

    // Basic Server-side Validation
    if (empty($email) || empty($password)) {
        $errorMessage = "Please enter both email and password.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } else {

        // --- MODIFIED LOGIC STARTS HERE ---

        // 1. First, try to authenticate as an ADMIN
        $sql_admin = "SELECT id, email, password FROM admin WHERE email = ?";
        $stmt_admin = $conn->prepare($sql_admin);

        if ($stmt_admin) {
            $stmt_admin->bind_param("s", $email);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            if ($admin = $result_admin->fetch_assoc()) {
                // Admin email found, verify password
                if ($password === $admin['password']) {
                    // Admin login successful!
                    
                    // Set admin-specific session data
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['user_role'] = 'admin'; // Best practice to set a role

                    // Redirect to the admin dashboard
                    header("Location: admin.php");
                    exit(); // IMPORTANT: Stop script execution
                }
            }
            $stmt_admin->close();
        } else {
             // Log error but don't show to user
            error_log("MySQL prepare error during admin login: " . $conn->error);
            $errorMessage = "An internal error occurred. Please try again.";
        }


        // 2. If not an admin, try to authenticate as a USER
        // This code only runs if the admin login above was not successful
        $sql_user = "SELECT id, password, first_name, last_name, email, profile_image FROM users WHERE email = ?";
        $stmt_user = $conn->prepare($sql_user);

        if ($stmt_user) {
            $stmt_user->bind_param("s", $email);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($user = $result_user->fetch_assoc()) {
                // User email found, verify the password
                if (password_verify($password, $user['password'])) {
                    // User login successful!

                    // Set user-specific session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['profile_image'] = $user['profile_image'];
                    $_SESSION['user_role'] = 'user'; // Best practice to set a role

                    // Redirect to the cars page as requested
                    header("Location: cars.php");
                    exit(); // IMPORTANT: Stop script execution

                }
            }
             $stmt_user->close();
        } else {
            // Log error but don't show to user
            error_log("MySQL prepare error during user login: " . $conn->error);
            $errorMessage = "An internal error occurred. Please try again.";
        }

        // 3. If the script reaches this point, both checks have failed.
        // Set the generic error message.
        if (empty($errorMessage)) {
            $errorMessage = "Invalid email or password.";
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - WheelBay</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-image: url('img/samuele-errico-piccarini-FMbWFDiVRPs-unsplash.jpg');
      background-size: cover;
      background-color: black;
      color: white;
    }

    .wheelbay-header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 25px;
      height: 80px;
      background-color: transparent;
    }

    .wheelbay-logo {
      width: 120px;
    }

    nav a {
      position: relative;
      font-size: 1.1em;
      color: #fff;
      text-decoration: none;
      padding: 6px 20px;
      transition: 0.5s;
    }

    nav a:hover {
      color: #0ef;
    }
     /* Added hover span effect from other pages */
     nav a span {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            border-bottom: 2px solid #0ef;
            border-radius: 15px;
            transform: scale(0) translateY(50px);
            opacity: 0;
            transition: .5s;
        }

        nav a:hover span {
            transform: scale(1) translateY(0);
            opacity: 1;
        }


    .wrapper {
      width: 400px;
      background: rgba(0, 0, 0, 0.7);
      box-shadow: 0 0 30px rgb(250, 250, 250);
      border-radius: 20px;
      padding: 40px;
       /* Removed overflow hidden for potential messages */
    }

    .wrapper:hover {
            animation: animate 1s linear infinite;
            box-shadow: 0 0 50px #0ef;
        }
     /* Keyframes for the hover animation */
     @keyframes animate {
         0% { box-shadow: 0 0 50px #0ef; }
         50% { box-shadow: 0 0 60px #0ef; }
         100% { box-shadow: 0 0 50px #0ef; }
     }


    h2 {
      font-size: 30px;
      color: #fff;
      text-align: center;
      margin-bottom: 20px;
    }

     .php-message-container {
        text-align: center;
        margin-bottom: 20px;
        min-height: 1.5em; /* Reserve space */
     }
     .php-error {
        color: #ff3333;
        font-size: 1em;
     }
     .php-success {
        color: #0ef;
        font-size: 1em;
     }


    .input-group {
      position: relative;
      margin: 30px 0;
      border-bottom: 2px solid #fff;
    }

    .input-group label {
      position: absolute;
      top: 50%;
      left: 5px;
      transform: translateY(-50%);
      font-size: 16px;
      color: #fff;
      pointer-events: none;
      transition: 0.5s;
    }

    .input-group input:focus ~ label,
    .input-group input:not(:placeholder-shown) ~ label,
     .input-group input:valid ~ label { /* Added valid state for label positioning */
      top: -5px;
      font-size: 12px;
    }

    .input-group input {
      width: 100%;
      height: 40px;
      font-size: 16px;
      color: #fff;
      padding: 0 5px;
      background: transparent;
      border: none;
      outline: none;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      width: 20px;
      height: 20px;
    }

    .remember {
      margin: -5px 0 15px 5px;
       /* Ensure checkbox is visible */
    }
     .remember label input {
         accent-color: #0ef;
         width: auto;
         padding: 0;
         margin-right: 5px;
         vertical-align: middle;
     }


    .remember label {
      color: #fff;
      font-size: 14px;
    }


    button {
      width: 100%;
      height: 40px;
      background: #0ef;
      box-shadow: 0 0 10px #0ef;
      font-size: 16px;
      color: #000;
      font-weight: 500;
      cursor: pointer;
      border-radius: 30px;
      border: none;
      outline: none;
      transition: 0.3s;
    }

    button:hover {
      background-color: #05c7b2;
       box-shadow: 0 0 15px #0ef; /* Enhanced hover effect */
    }


    .signUp-link {
      text-align: center;
      margin-top: 20px;
      color: #fff;
    }

    .signUp-link a {
      color: #0ef;
      text-decoration: none;
      font-weight: 500;
    }

    .signUp-link a:hover {
      text-decoration: underline;
    }

     /* Responsive adjustment for smaller headers */
     @media (max-width: 768px) {
         .wheelbay-header {
             padding: 0 10px;
         }
         .wheelbay-logo {
             width: 100px;
         }
         nav a {
             padding: 6px 10px;
             font-size: 1em;
         }
         .wrapper {
             width: 90%;
             padding: 30px 20px;
         }
         h2 {
             font-size: 24px;
         }
     }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="wheelbay-header">
    <img class="wheelbay-logo" src="img/Logo01.png" alt="WheelBay Logo" />
    <nav>
      <a href="home.html">Home<span></span></a>
      <a href="cars.html">Cars<span></span></a>
      <a href="about.html">About<span></span></a>
      <a href="prompt.html">Register<span></span></a> <?php /* Link to register.php */ ?>
      <a href="login.php">Login<span></span></a> <?php /* Link to login.php - active page */ ?>
    </nav>
  </header>

  <!-- Login Form -->
  <div class="wrapper">
    <form id="loginForm" method="post" action=""> <?php /* Form submits to self */ ?>
      <h2>Login</h2>

       <?php if (!empty($errorMessage)): ?>
            <div class="php-message-container"><p class="php-error"><?php echo htmlspecialchars($errorMessage); ?></p></div>
       <?php endif; ?>

       <?php if (!empty($successMessage)): ?>
            <div class="php-message-container"><p class="php-success"><?php echo htmlspecialchars($successMessage); ?></p></div>
       <?php endif; ?>


      <div class="input-group">
         <!-- Add name attribute, type email, and PHP for repopulating email -->
        <input type="email" id="loginEmail" name="email" required placeholder=" " value="<?php echo htmlspecialchars($prefillEmail); ?>" />
        <label for="loginEmail">Email</label>
      </div>
      <div class="input-group">
         <!-- Add name attribute -->
        <input type="password" id="loginPassword" name="password" required placeholder=" " />
        <label for="loginPassword">Password</label>
        <img src="img/eye.png" class="toggle-password" onclick="togglePassword('loginPassword')" alt="Toggle Password" />
      </div>
      <div class="remember">
         <!-- Add name attribute if you want to process this on the server -->
        <label><input type="checkbox" name="remember" /> Remember me</label>
      </div>
       <!-- Change type to submit -->
      <button type="submit">Login</button>

      <div class="signUp-link">
        <p>Don't have an account? <a href="registeruser.php" class="signUpBtn-link">Register</a></p> <?php /* Link to register.php */ ?>
      </div>
    </form>
  </div>

  <script>
      // Password toggle functionality (Client-side)
    function togglePassword(inputId) {
      const input = document.getElementById(inputId);
      const icon = input.nextElementSibling; // Assumes icon is the immediately following sibling
      if (input.type === "password") {
        input.type = "text";
         // Change icon source (you need eye-slash.png)
        icon.src = "css/img/eye-slash.png"; // Make sure you have this image
         icon.alt = "Hide Password";
      } else {
        input.type = "password";
         // Change icon source
        icon.src = "css/img/eye.png"; // Make sure you have this image
         icon.alt = "Show Password";
      }
    }

      // Client-side validation and submit handler removed.
      // The form will now submit to PHP by default when the button is clicked.
      // PHP handles the validation, authentication, and redirection.

      // The original JS had some client-side validation alerts.
      // You can keep those for immediate feedback *before* submission if you like,
      // by adding a 'submit' event listener and potentially calling event.preventDefault()
      // if client-side validation fails. However, PHP validation is the necessary security layer.

       // Example of adding back client-side validation (optional)
       /*
        document.getElementById("loginForm").addEventListener("submit", function (e) {
           const email = document.getElementById("loginEmail").value;
           const password = document.getElementById("loginPassword").value;
           let clientValid = true;

           if (!email.includes("@") || !email.includes(".")) {
             alert("Please enter a valid email address.");
             clientValid = false;
           }

           // Note: Client-side password length check is less useful if PHP requires hashing and complexity.
           // A simple check is fine, but don't rely solely on it.
           if (password.length < 1) { // Basic check for empty password field
              // alert("Password field cannot be empty.");
              // You'd typically show this next to the field, not as an alert, matching PHP style
              // For this example, relying on HTML 'required' and PHP validation is simpler.
           }


           if (!clientValid) {
             e.preventDefault(); // Stop submission if client-side fails
           }
         });
       */

  </script>
</body>
</html>