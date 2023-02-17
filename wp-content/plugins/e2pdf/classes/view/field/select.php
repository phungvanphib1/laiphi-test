<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<select <?php foreach ($this->tpl_args->get('field') as $key => $value) { ?><?php if (($key === 'disabled' && $value != false) || $key != 'disabled') { ?><?php echo $key; ?>="<?php echo esc_attr($value); ?>" <?php } ?><?php } ?>>
    <?php if ($this->tpl_args->get('empty')) { ?>
        <option <?php if ($this->tpl_args->get('value') == '') { ?>selected="selected"<?php } ?> value=""><?php echo $this->tpl_args->get('empty') ?></option>
    <?php } ?>
    <?php foreach ($this->tpl_args->get('options') as $key => $value) { ?>
        <?php if (is_array($value)) { ?>
            <option <?php
            if (isset($value['subfield'])) {
                foreach ($value['subfield'] as $sub_key => $sub_value) {
                    ?><?php echo $sub_key; ?>="<?php echo esc_attr($sub_value); ?>" <?php
                    }
                }
                ?><?php if ($this->tpl_args->get('value') == $value['key']) { ?>selected="selected"<?php } ?> value="<?php echo esc_attr($value['key']); ?>"><?php echo $value['value']; ?></option>
            <?php } else { ?>
            <option <?php if ($this->tpl_args->get('value') == $key) { ?>selected="selected"<?php } ?> value="<?php echo esc_attr($key); ?>"><?php echo $value; ?></option>
        <?php } ?>
    <?php } ?>
</select>
