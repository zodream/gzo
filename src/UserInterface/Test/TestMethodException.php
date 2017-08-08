<?php
defined('APP_DIR') or exit();
?>
    /**
     * Generated from @assert <?=$annotation?>.
     *
     * @covers <?=$className?>::<?=$origMethodName?>
     * @expectedException <?=$expected?>
     */
    public function test<?=$methodName?>() {
        $this->object-><?=$origMethodName?>(<?=$arguments?>);
    }
