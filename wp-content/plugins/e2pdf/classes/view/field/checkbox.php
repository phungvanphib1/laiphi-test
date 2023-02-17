<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<fieldset class="<?php echo isset($checkbox_field['class']) ? $checkbox_field['class'] : ''; ?>">
    <legend class="screen-reader-text"><span><?php _e($this->tpl_args->get('title')); ?></span></legend>
    <label>
        <?php $checkbox_field = $this->tpl_args->get('field'); ?>
        <?php if (isset($checkbox_field['name']) && preg_match('/\[\]$/', $checkbox_field['name'])) { ?>
        <?php } else { ?>
            <input type="hidden" name="<?php echo isset($checkbox_field['name']) ? esc_attr($checkbox_field['name']) : '' ?>" value="<?php echo esc_attr($this->tpl_args->get('default_value')); ?>"/>
        <?php } ?>
        <input type="checkbox" <?php foreach ($this->tpl_args->get('field') as $key => $value) { ?><?php if (($key === 'disabled' && $value != false) || $key != 'disabled') { ?><?php echo $key; ?>="<?php echo esc_attr($value); ?>" <?php } ?><?php } ?><?php echo $this->tpl_args->get('value') == $this->tpl_args->get('checkbox_value') ? 'checked="checked"' : '' ?>
               value="<?php echo esc_attr($this->tpl_args->get('checkbox_value')); ?>"/> <?php echo isset($checkbox_field['placeholder']) ? $checkbox_field['placeholder'] : '' ?>
    </label>
</fieldset>
