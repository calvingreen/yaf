<?php
	class IndexController extends Yaf_Controller_Abstract {
   		public function indexAction() {//默认Action
   			new Users_Weibo();
   			Yaf_Loader::getInstance()->registerLocalNamespace(array('Tools'));
   			new Tools_Format();

			$test = Yaf_Loader::getInstance();
			var_dump($test);
			Com::Show();
			$this->getView()->assign("content", "Hello World");
   		}
	}
?>
