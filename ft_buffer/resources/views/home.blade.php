<?php require $_SERVER['DOCUMENT_ROOT'] . '/../resources/views/header.php' ?>

<body>
    <div style="display : flex;justify-content: center;">
        <?php
        if (isset($_SESSION['access_token'])) {
        ?>
            <div style="padding : 5px">
                <a href="/scheduled_posts">scheduled posts</a>
            </div>
            <div style="padding : 5px">
                <form method="POST" action="/logout">
                    @csrf
                    <input type="submit" name="logout" value="logout">
                </form>
            </div>
        <?php
        }
        ?>
    </div>
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
        <?php
        if (isset($_SESSION['_oauth_error']))
            echo '<p style="background-color : red">' . $_SESSION['_oauth_error'] . '</p>';
        unset($_SESSION['_oauth_error']);
        ?>
    <?php } else { ?>
        <div class="PostsContainer">
            <div>
                <h1>Welcome <?php echo $_SESSION['name'] ?> </h1>
            </div>
            <div>
                <p>get access to <?php echo isset($_SESSION['accounts_length']) ? $_SESSION['accounts_length'] : 0  ?> pages</p>
            </div>
            <?php
            if (isset($_SESSION['accounts'])) {
            ?>
                <form method="POST" action="/submit_post">
                    <?php
                    if (isset($_SESSION['_error']))
                        echo '<p style="background-color : red">' . $_SESSION['_error'] . '</p>';
                    else if (isset($_SESSION['_success']))
                        echo '<p style="background-color : green">' . $_SESSION['_success'] . '</p>';
                    unset($_SESSION['_error'], $_SESSION['_success']);
                    ?>
                    @csrf
                    <label for="pages">select working pages:</label>
                    <div id="pages" class="Pages">
                        <?php
                        foreach ($_SESSION['accounts'] as $key => $value) {
                        ?>
                            <div class="Page">
                                <div>
                                    <p>Choose </p>
                                    <input id="<?php echo 'check' . $key ?>" type="checkbox" onClick="onPageSelected(this)" name="<?php echo $value['id'] ?>" />
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
                            <div style="display : flex;padding : 4;">
                                <input type="checkbox" onClick="onScheduledSelected(this)" name="scheduled" />
                                <p>scheduled post ?</p>
                            </div>
                            <div id="ContainerScheduledDate" style="display : flex;padding : 4;display : none;">
                                <p>select date</p>
                                <input id="scheduled_date" name="scheduled_date" type="datetime-local" id="start" name="trip-start">
                            </div>
                            <p><label for="post">Share post:</label></p>
                            <textarea id="post" placeholder="post to share on selected pages" name="post" rows="4" cols="50"></textarea>
                        </div>
                    </div>
                <?php
                }
                ?>
                <input type="submit" name="submit" value="send" />
                </form>
        </div>
    <?php } ?>
    <script>
        if (window.location.hash != '')
            window.location.hash = '';

        function onPageSelected(item) {
            let allPages = document.getElementById('pages').getElementsByTagName('input');
            for (let i = 0; i < allPages.length; i++) {
                if (allPages[i].id != item.id)
                    allPages[i].checked = false;
            }
        }

        function onScheduledSelected(item) {
            if (item.checked == true)
                ContainerScheduledDate.style.display = 'block';
            else
                ContainerScheduledDate.style.display = 'none';
        }
    </script>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/../resources/views/footer.php' ?>