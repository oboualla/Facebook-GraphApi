<?php require '/app/resources/views/header.php' ?>

<body>
    <?php
    if (!isset($_SESSION['access_token'])) {
    ?>
        <div class="loginContainer">
            <div>
                <h1>Welcome to ft_Buffer</h1>
            </div>
            <div>
                <a href="<?php echo $loginUrl ?>">Login with facebook</a>
            </div>
        </div>
    <?php } else { ?>
        <div class="PostsContainer">
            <div>
                <h1>Welcome <?php echo $_SESSION['name'] ?> </h1>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <input type="submit" name="logout" value="logout">
            </form>
            <div>
                <p>get access to <?php echo isset($_SESSION['accounts_length']) ? $_SESSION['accounts_length'] : 0  ?> pages</p>
            </div>
            <?php
            if (isset($_SESSION['accounts'])) {
            ?>
                <form method="POST" action="/logout">
                    <label for="pages">working a pages:</label>
                    <div id="pages" class="Pages">
                        <?php
                        foreach ($_SESSION['accounts'] as $key => $value) {
                        ?>
                            <div class="Page">
                                <div>
                                    <p>Choose</p>
                                    <input type="checkbox" value="" name="<?php echo $value['id'] ?>" />
                                </div>
                                <div>
                                    <p>name <b id="name"><?php echo $value['name'] ?></b></p>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                <?php
            }
                ?>

                <?php
                if ($_SESSION['accounts_length'] > 0) {
                ?>
                    <div class="Pages">
                        <div class="Page">
                            <p><label for="post">Share post:</label></p>
                            <textarea id="post" placeholder="post to share on selected pages" name="post" rows="4" cols="50"></textarea>
                        </div>
                    </div>
                <?php
                }
                ?>
                </form>
        </div>
    <?php } ?>
    <script>
        if (window.location.hash != '')
            window.location.hash = ''
    </script>
    <?php require '/app/resources/views/footer.php' ?>