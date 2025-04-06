<!--User will be brought here from the welcome page-->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Document</title>
</head>

<body>
    <img src="assets/images/brief-case.png" alt="">

    <div id="main-content">
        <p> Dead or no deal</p>

        <div id="game-main-content">



            <div id="left-prices">
                <?php

                for ($i = 0; $i < 14; $i++){
                        echo '<div class="price-container"> </div>';
                    }

                ?>
            </div>

            <div id="case-area">

                       <?php

                for ($i = 0; $i < 3; $i++){
                        echo '<div class="price-container"> 
                        
                         <img src="assets/images/brief-case.png" alt="">
                         <img src="assets/images/brief-case.png" alt="">
                          <img src="assets/images/brief-case.png" alt="">
                           <img src="assets/images/brief-case.png" alt="">
                            <img src="assets/images/brief-case.png" alt="">
                        </div>';
                    }

                ?>  

            </div>

            <div id="right-prices">

                <?php

                for ($i = 0; $i < 14; $i++){
                        echo '<div class="price-container"> </div>';
                    }

                ?>

            </div>


        </div>
    </div>





</body>

</html>