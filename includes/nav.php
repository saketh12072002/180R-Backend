<body>

<style>
    a:hover{
      background-color:	 #333333 ;
      
    }
</style>
    
<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="index.php" class="navbar navbar-brand" style="padding-left: 5px;"><h3><img src="css/180Research.png" width="35" height="35" class="d-inline-block align-top" alt="180R"> 180R Registration </h3></a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a hover href="index.html" class="nav-link active">Home</a>
                </li>
                <?php
                
                    if(isset($_SESSION['Email']) || isset($_COOKIE['email']))
                    {
                ?>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link active">Logout</a>
                    </li>
                <?php
                    }
                    else
                    {
                ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link active">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link active">Register</a>
                    </li>
                <?php
                    }
                
                ?>
            </ul>
           
        </div>
    </nav>
    </body>
