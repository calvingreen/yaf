<?php
class IndexController extends Yaf_Controller_Abstract
{

    protected $mc;


    public function indexAction()
    { //默认Action

        if (!$this->mc) {
            $this->mc = new Memcached();
            //设置环形哈希算法
            $this->mc->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $this->mc->setOption(Memcached::OPT_NO_BLOCK, true);
            $this->mc->setOption(Memcached::OPT_CONNECT_TIMEOUT, 200);
            $this->mc->setOption(Memcached::OPT_POLL_TIMEOUT, 50);
            //连接服务器
            $servers = explode(' ', '10.39.6.245:11211 10.39.6.248:11211');

            foreach ($servers as $key => $val) {
                $v = explode(':', $val);
                $this->mc->addServer($v[0], $v[1]);
            }
        }

        $this->mc->set('aaa', '111111', 60000);

        echo $this->mc->get('aaa');

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
