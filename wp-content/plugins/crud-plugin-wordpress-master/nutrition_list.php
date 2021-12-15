<?php

function nutrition_list() {
    ?>
    <style>
        table {
            border-collapse: collapse;


        }

        table, td, th {
            border: 1px solid black;
            padding: 20px;
            text-align: center;
        }
    </style>
    <div class="wrap">
        <table>
            <tr>
                <h2>Nutritions List</h2>
            </tr>
            <thead>
            <tr>
                <th>Sr.No</th>
                <th>type</th>
                <th>Title</th>
                <th>Time Duration</th>
                <th>File</th>
                <th>Thumbnail Image</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix.'nutrition';
            $nutritions = $wpdb->get_results("SELECT * from $table_name");
            foreach ($nutritions as $nutrition) {
                ?>
                <tr>
                    <td><?= $nutrition->id; ?></td>
                    <td><?= $nutrition->type; ?></td>
                    <td><?= $nutrition->title; ?></td>
                    <td><?= $nutrition->time_duration; ?></td>
                    <td><?= $nutrition->path; ?></td>
                    <td><?= $nutrition->thumbnail_img; ?></td>
                    <td><a href="<?php echo admin_url('admin.php?page=Nutrition_Update&id=' . $nutrition->id); ?>">Update</a> </td>
                    <td><a href="<?php echo admin_url('admin.php?page=Nutrition_Delete&id=' . $nutrition->id); ?>"> Delete</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php

}
add_shortcode('short_nutrition_list', 'nutrition_list');
?>