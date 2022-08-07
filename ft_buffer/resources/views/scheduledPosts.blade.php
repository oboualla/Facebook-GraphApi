<?php require $_SERVER['DOCUMENT_ROOT'] . '/../resources/views/header.php' ?>

<body>
    <div style="display : flex;justify-content: center;">
        <div style="padding : 5px">
            <a href="/">Home</a>
        </div>
        <?php
        if (isset($_SESSION['access_token'])) {
        ?>

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
    <div class="PostsContainer">
        <div>
            <h1>Scheduled Posts</h1>
        </div>
        <div>
            <p>you have <?php echo count($data) ?> schedule jobs.</p>
        </div>
        <div>
            <?php
            if (isset($_SESSION['_post_error']))
                echo '<p style="background-color : red">' . $_SESSION['_post_error'] . '</p>';
            else if (isset($_SESSION['_post_success']))
                echo '<p style="background-color : green">' . $_SESSION['_post_success'] . '</p>';
            unset($_SESSION['_post_error'], $_SESSION['_post_success']);
            ?>
        </div>
        <div id="pages" class="Pages">
            <?php
            foreach ($data as $key => $value) {
            ?>
                <div class="Page">
                    <div>
                        <p>id <b id="name"><?php echo $value->id ?></b></p>
                    </div>
                    <div>
                        <p>page id <b id="name"><?php echo $value->page_id ?></b></p>
                    </div>
                    <div>
                        <p>message <b id="name"><?php echo $value->message ?></b></p>
                    </div>
                    <div>
                        <p>adding date <b id="name"><?php echo $value->pushing_date ?></b></p>
                    </div>
                    <div>
                        <p>created date <b id="name"><?php echo $value->created_at ?></b></p>
                    </div>
                    <div>
                        <p>status <b id="name" style="color : <?php echo $value->status == 'pending' ? '#82611a' : ($value->status == 'success' ? '#187d34' : 'red') ?>"><?php echo $value->status ?></b></p>
                    </div>
                    <div style="display : flex;justify-content:space-between;">
                        <div>
                            <form action="/delete_post" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="<?php echo $value->id ?>" />
                                <input type="submit" name="submit" value="delete" />
                            </form>
                        </div>
                        <?php
                        if ($value->status == 'error') {
                        ?>
                            <div>
                                <form action="/retry_post" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="<?php echo $value->id ?>" />
                                    <input type="submit" name="submit" value="retry" />
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <script>
    </script>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/../resources/views/footer.php' ?>