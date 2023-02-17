<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<?php foreach ($this->tpl_args->get('options') as $key => $value) { ?>
    <?php if (is_array($value)) { ?>
        <div>
            <input type="radio" <?php foreach ($this->tpl_args->get('field') as $sub_key => $sub_value) { ?><?php if (($sub_key === 'disabled' && $sub_value != false) || $sub_key != 'disabled') { ?><?php echo $sub_key; ?>="<?php echo esc_attr($sub_value); ?>" <?php } ?><?php } ?> <?php
            if (isset($value['subfield'])) {
                foreach ($value['subfield'] as $sub_key => $sub_value) {
                    ?><?php echo $sub_key; ?>="<?php echo esc_attr($sub_value); ?>" <?php
                       }
                   }
                   ?><?php if ($this->tpl_args->get('value') == $value['key']) { ?>checked="checked"<?php } ?> value="<?php echo esc_attr($value['key']); ?>"> <?php echo $value['value']; ?>
        </div>
    <?php } else { ?>
        <div>
            <input type="radio" <?php foreach ($this->tpl_args->get('field') as $sub_key => $sub_value) { ?><?php if (($sub_key === 'disabled' && $sub_value != false) || $sub_key != 'disabled') { ?><?php echo $sub_key; ?>="<?php echo esc_attr($sub_value); ?>" <?php } ?><?php } ?> <?php if ($this->tpl_args->get('value') == $key) { ?>checked="checked"<?php } ?> value="<?php echo esc_attr($key); ?>"> <?php echo $value; ?>
        </div>
    <?php } ?>
<?php } ?>
