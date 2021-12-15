<script type="text/javascript">
    window._nsl.push(function ($) {
        $(document).ready(function () {
            var $container = $('#<?php echo $containerID; ?>');
            $container.find('.nsl-container')
                .addClass('nsl-container-embedded-login-layout-below')
                .css('display', 'block');

            $container
                .appendTo($container.closest('form').parent());
        });
    });
</script>
<?php
$style = '
    {{containerID}} {
        margin-top: 20px;
    }
    
    {{containerID}} .nsl-container {
        display: none;
    }

    {{containerID}} .nsl-container-login-layout-below {
        clear: both;
        padding: 20px 0 0;
    }';
?>
<style type="text/css">
    <?php echo str_replace('{{containerID}}','#' . $containerID, $style); ?>
</style>
<?php
$style = '
    {{containerID}} .nsl-container {
        display: block;
    }';
?>
<noscript>
    <style type="text/css">
        <?php echo str_replace('{{containerID}}','#' . $containerID, $style); ?>
    </style>
</noscript>