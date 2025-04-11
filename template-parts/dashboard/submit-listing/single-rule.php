<?php
$id = $args['id'];
$rulesText = $args['rulesText'];
?>
<div class="single-rules-row mb-5">
    <label class="label-condition"><?php echo $rulesText; ?></label>
    <input type="hidden" name="rule[<?php echo $id; ?>][title]" value="<?php echo $rulesText; ?>">
    <div class="rules-fileds-sections">
        <div class="form-group">
            <label class="control control--radio radio-tab">
                <input name="rule[<?php echo $id; ?>][is_allowed]" value="yes" type="radio">
                <span class="control-text"><?php esc_html_e('Yes','homey-child'); ?></span>
                <span class="control__indicator"></span>
                <span class="radio-tab-inner"></span>
            </label>
        </div>
        <div class="form-group">
            <label class="control control--radio radio-tab">
                <input name="rule[<?php echo $id; ?>][is_allowed]" value="no" checked="checked" type="radio">
                <span class="control-text"><?php esc_html_e('No','homey-child'); ?></span>
                <span class="control__indicator"></span>
                <span class="radio-tab-inner"></span>
            </label>
        </div>
        <a href="#" class="remove-btn-single-rule"><i class="fa fa-trash"></i></a>
    </div>
</div>