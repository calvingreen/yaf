<?php
	class Com {
		static function Show ( $input = '' ) {
			if(empty($input)){
				echo 'tell me what want to show</br>';
			}else{
				echo $input;
			}
		}
	}
?>