<body>
    <div>
        <div>
            Hello {{ $data['name'] }},
        </div>
        <div>
            <?php
            if ($data['status'] == 'success') {
                echo '<p>this message is to inform you that your post on page (' . $data['page_id'] .  ') is been published. </p>';
            } else
                echo '<p>this message is to inform you that your post on page (' . $data['page_id'] .  ') is been refused. </p>';
            ?>
        </div>
    </div>
</body>