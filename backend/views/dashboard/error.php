<?php

use common\helpers\Html;

$this->title = $code. ' ' .$name;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="alert alert-danger">
	<?php echo nl2br(Html::encode($message)) ?>
</div>