<?php if(!empty($debugMessages)):?>
<h3>Debug Messages</h3>
<ul>
<?php foreach($debugMessages as $dm):?>
    <li>{{dm}}</li>
<?php endforeach;?>
</ul>
<?php endif;?>