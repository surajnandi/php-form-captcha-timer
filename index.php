<?php
include('connection.php');

if (isset($_POST["submit"])) {

    $sessionCaptcha = $_SESSION['captcha'];
    $formCaptcha = $_POST['captcha'];

    $name = $_POST["name"];
    $email = $_POST["email"];
    $dob = $_POST["dob"];
    $details = $_POST["details"];


    if (
        trim($name) != "" and trim($email) != "" and trim($dob) != ""
        and trim($details) != ""
    ) {
        //Sanitizes whatever is entered
        $name = stripcslashes($name);
        $email = stripcslashes($email);
        $dob = stripcslashes($dob);
        $details = stripcslashes($details);

        $name = strip_tags($_POST["name"]);
        $email = strip_tags($_POST["email"]);
        $dob = strip_tags($_POST["dob"]);
        $details = strip_tags($_POST["details"]);

        $name = mysqli_real_escape_string($conn, $name);
        $email = mysqli_real_escape_string($conn, $email);
        $dob = mysqli_real_escape_string($conn, $dob);
        $details = mysqli_real_escape_string($conn, $details);


        $sql = "SELECT * FROM users WHERE (email='$email');";

        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) > 0) {

            $row = mysqli_fetch_assoc($res);
            if ($email == isset($row['email'])) {
                // echo "email already exists";
                $_SESSION['status'] = "You already submitted form. Thank you!";
                $_SESSION['status_code'] = "warning";
                // header("Location:index.php");

                echo '<script type="text/javascript">
                       window.location = "./"
                   </script>';
                exit();
            }
        } else {

            if ($sessionCaptcha == $formCaptcha) {
                //do your insert code here or do something (run your code)
                $sql = "INSERT INTO users (name, email, dob, details) VALUES ('$name','$email','$dob','$details')";
                mysqli_query($conn, $sql);
                $current_id = mysqli_insert_id($conn);
                if (!empty($current_id)) {

                    $_SESSION['status'] = "Successfully submitted your form. Thank you!";
                    $_SESSION['status_code'] = "success";
                    // header("Location:index.php");
                    echo '<script type="text/javascript">
                       window.location = "./"
                   </script>';
                    exit();
                }
            } else {
                // echo '<script>alert("Invalid Captcha")</script>';
                $_SESSION['status'] = "Invalid Captcha Code";
                $_SESSION['status_code'] = "warning";
                // header("Location:index.php");
                echo '<script type="text/javascript">
                       window.location = "./"
                   </script>';
                exit();
            }
        }
    }
}

// timer
$result = mysqli_query($conn, "SELECT * FROM timer ORDER BY id DESC");
while ($res = mysqli_fetch_array($result)) {
    $h = $res['h'];
    $m = $res['m'];
    $s = $res['s'];
}

?>

<!-- -------------------------------------------------------------------- -->
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Aelum Project</title>

    <!-- jquery ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.5/dist/sweetalert2.all.min.js"></script>
</head>
<style>
    .container {
        width: 50%;
        border: 1px solid blue;
        margin-top: 20px;
    }
</style>

<body>

    <div class="container">
        <div class="card-body">
            <h5 class="text-center">Fill Up All The Details Within 3 Minutes [<span id="demo"></span>]</h5>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="form-group mt-4 mb-3">
                    <label for="">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter Name" required>
                </div>

                <div class="form-group mt-4 mb-3">
                    <label for="exampleInputEmail1">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter Email id" required>
                </div>

                <div class="form-group mt-4 mb-3">
                    <label for="">Date of Birth</label>
                    <input type="text" name="dob" id="dob" class="form-control" placeholder="Enter Date of Birth" required>
                </div>

                <div class="form-group mb-3">
                    <label for="exampleFormControlTextarea1">About Yourself</label>
                    <textarea class="form-control" name="details" rows="3" required></textarea>
                </div>

                <div class="form-group row mb-3">
                    <div class="col-sm-9">
                        <label for="exampleFormControlTextarea1">Enter Captcha</label>
                        <input type="text" class="form-control" name="captcha" id="captcha" placeholder="Enter captcha code" required>
                    </div>
                    <div class="col-sm-3">
                        <label>Captcha Code</label>
                        <img style="padding-top: 4px;" src="captcha.php" alt="Captcha Code">
                    </div>
                </div>

                <input type="submit" name="submit" value="Submit" class="btn btn-primary">
            </form>
        </div>
    </div>

    <!-- --------------------------------------------------------------- -->
    <!-- Alert Message -->
    <?php if (isset($_SESSION['status']) && !empty($_SESSION['status'])) { ?>

        <script>
            Swal.fire({
                position: 'center',
                icon: '<?php echo $_SESSION['status_code']; ?>',
                title: '<?php echo $_SESSION['status']; ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>

    <?php
        unset($_SESSION['status']);
    }
    ?>

    <!-- --------------------------------------------------------------- -->
    <!-- Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dob').datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
            });
        });
    </script>

    <!-- -------------------------------------------------------------- -->
    <!-- timer -->
    <script>
        //define your time in second
        var c = <?= $s ?>;
        // var c = 10;
        var t;
        timedCount();

        function timedCount() {

            var hours = parseInt(c / 3600) % 24;
            var minutes = parseInt(c / 60) % 60;
            var seconds = c % 60;

            var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);


            $('#demo').html(result);
            if (c == 0) {
                //setConfirmUnload(false);
                window.location = "expire.php";
            }
            c = c - 1;
            t = setTimeout(function() {
                    timedCount()
                },
                1000);
        }
    </script>
</body>

</html>