<?php

/**
 * View的基类, 用来渲染页面
 *
 */

class Base_View {
    const LEFT_DELIMITER  = '{=';
    const RIGHT_DELIMITER = '=}';
    const PATH_APP_TPL = '/templates';
    const PATH_APP_TPC = '/templates_c';
    const PATH_MYPLUGINS = '/libs/view/myplugins';
    private static $tpl = NULL;

    public function __construct () {
        if (NULL === self::$tpl) {
            self::$tpl = new Smarty();
            self::$tpl->setTemplateDir(Bd_AppEnv::getEnv('code') . self::PATH_APP_TPL);
            self::$tpl->setCompileDir(Bd_AppEnv::getEnv('cache') . self::PATH_APP_TPC);
            self::$tpl->addPluginsDir(APP_PATH . self::PATH_MYPLUGINS);
            self::$tpl->left_delimiter  = self::LEFT_DELIMITER;
            self::$tpl->right_delimiter = self::RIGHT_DELIMITER;
            self::$tpl->compile_locking = false;
            
            //自动转义html标签，防止xss，不转义使用{=$data nofilter=}
            function escFilter ($content, $smarty) {
                return htmlspecialchars($content,ENT_QUOTES,'UTF-8');
            }
            self::$tpl->registerFilter('variable', 'escFilter');
        }
    }

    public function setView($key, $value) {
        self::$tpl->assign($key, $value);
    }

    public function __call ($func, $args) {
        return call_user_func_array(array(&self::$tpl, $func), $args);
    }
}
