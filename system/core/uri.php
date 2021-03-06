<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

class core_uri
{
	var $params = array(
		'sep_value' => '-',	// 赋值分隔符
		'sep_var' => '__',	// 变量分割符
		'sep_act' => '/'	// 动作分割符
	);

	// 默认控制器
	var $default_vars = array(
		'app_dir' => 'explore',
		'controller' => 'main',
		'action' => 'index'
	);

	var $app_dir = '';
	var $controller = '';
	var $action = '';

	var $request_main = '';
	var $index_script = '';
	
	var $args_var_str = '';

	public function __construct()
	{
		$this->index_script = '?/';

		if ($_SERVER['REQUEST_URI'])
		{
			if (isset($_SERVER['HTTP_X_REWRITE_URL']))
			{
				$request_main = $_SERVER['HTTP_X_REWRITE_URL'];
			}
			else
			{
				$request_main = $_SERVER['REQUEST_URI'];
			}

			$requests = explode($this->index_script, $request_main);

			if (count($requests) == 1 AND dirname($_SERVER['SCRIPT_NAME']) != '/')
			{
				$request_main = preg_replace('/^' . preg_quote(dirname($_SERVER['SCRIPT_NAME']), '/') . '/i', '', $request_main);
			}
			else if (count($requests) == 2)
			{
				if ($requests[0] != '/')
				{
					$request_main = str_replace($requests[0], '', $request_main);
				}

				$request_main = str_replace($this->index_script, '', $request_main);
			}
		}
		else if ($_SERVER['QUERY_STRING'])
		{
			$request_main = $_SERVER['QUERY_STRING'];
		}

		$request_main = ltrim($request_main, "/\\");

		$base_script = basename($_SERVER['SCRIPT_NAME']);

		if (!strstr($request_main, '=') AND !strstr($request_main, '/') AND !strstr($request_main, '-') AND !strstr($request_main, '.'))
		{
			$request_main .= '/';
		}

		if (strstr($base_script, '.php'))
		{
			$request_main = str_replace($base_script . '/', '', $request_main);
		}

		$this->request_main = $request_main;
	}

	public function set_rewrite()
	{
		if (!$this->request_main OR $this->index_script == $this->request_main)
		{
			$this->controller = 'main';
			$this->action = 'index';

			return $this;
		}

		$request = explode('?', $this->request_main, 2);

		if (count($request) == 1)
		{
			$request = explode('&', $this->request_main, 2);
		}

		$uri = array(
			'first' => array_shift($request),
			'last' => ltrim(implode($request), '?')
		);

		if ($uri['last'])
		{
			parse_str($uri['last'], $query_string);

			foreach ($query_string AS $key => $val)
			{
				if (!$_GET[$key])
				{
					if (! strstr($val, '%'))
					{
						$_GET[$key] = $val;
					}
					else
					{
						$_GET[$key] = urldecode($val);
					}
				}
			}
		}

		// 如果 $uri['last'] 里有 'app' key 则为插件
		$is_plugin = isset($_GET['app']);

		$request = explode($this->params['sep_act'], $uri['first']);

		$uri['first'] = array(
			'pattern' => '',
			'args' => $request
		);

		$__app_dir = $this->default_vars['app_dir'];	// 应用目录
		$this->controller = $this->default_vars['controller'];	// 控制器
		$this->action = $this->default_vars['action'];	// 动作

		$this->args_var_str = '';

		// 删除空值		
		foreach ($uri['first']['args'] AS $key => $val)
		{
			if (strstr($val, $this->params['sep_value']) AND !$start_key)
			{
				$start_key = $key;
			}
			else if ($start_key)
			{
				$uri['first']['args'][$start_key] .= $this->params['sep_act'] . $val;

				unset($uri['first']['args'][$key]);
			}
		}
		
		$args_count = count($uri['first']['args']);
		
		switch ($args_count)
		{
			default:
				return $this;
			break;

			case 1:
				$this->args_var_str = end($uri['first']['args']);
			break;

			case 2:
				$this->args_var_str = end($uri['first']['args']);
				
				$__app_dir = $uri['first']['args'][0] ? $uri['first']['args'][0] : $this->default_vars['app_dir'];	// 应用目录
			break;

			case 3:
				$this->args_var_str = end($uri['first']['args']);
				
				$__app_dir = $uri['first']['args'][0] ? $uri['first']['args'][0] : $this->default_vars['app_dir'];	// 应用目录

				if ($is_plugin)
				{
					// 插件 controller
					$app_root_path = ROOT_PATH . 'plugins/' . $__app_dir . '/controller/';
				}
				else
				{
					// 普通 controller
					$app_root_path = ROOT_PATH . 'app/' . $__app_dir . '/';
				}

				if (file_exists($app_root_path . $uri['first']['args'][1] . '.php'))
				{
					$this->controller = $uri['first']['args'][1];	// 控制器
				}
				else
				{
					$this->controller = $this->default_vars['controller'];	// 控制器
					$this->action = $uri['first']['args'][1];	// 动作
				}
			break;

			case 4:
				$this->args_var_str = end($uri['first']['args']);
				
				$__app_dir = $uri['first']['args'][0] ? $uri['first']['args'][0] : $this->default_vars['app_dir'];	// 应用目录
				$this->controller = $uri['first']['args'][1] ? $uri['first']['args'][1] : $this->default_vars['controller'];	// 控制器
				$this->action = $uri['first']['args'][2] ? $uri['first']['args'][2] : $this->default_vars['action'];	// 动作
			break;
			
			case 5:
				$this->args_var_str = end($uri['first']['args']);
						
				$__app_dir = $uri['first']['args'][0] ? $uri['first']['args'][0] : $this->default_vars['app_dir'];	// 应用目录
				$this->controller = $uri['first']['args'][2] ? $uri['first']['args'][1] . '/' . $uri['first']['args'][2] : $this->default_vars['controller'];	// 控制器
				$this->action = $uri['first']['args'][3] ? $uri['first']['args'][3] : $this->default_vars['action'];	// 动作
			break;
		}

		if ($is_plugin)
		{
			 // 插件 controller
			$this->app_dir = ROOT_PATH . 'plugins/' . $__app_dir . '/controller/';
		}
		else
		{
			// 普通 controller
			$this->app_dir = ROOT_PATH . 'app/' . $__app_dir . '/';
		}

		$_GET['c'] = $this->controller;
		$_GET['act'] = $this->action;
		$_GET['app'] = $__app_dir;
		
		return $this;
	}
	
	public function parse_args()
	{
		if ($args_var_str = $this->args_var_str)
		{			
			if (defined('WECENTER_URI_SET_LAST_STRING_TO_ID'))
			{
				$_GET['id'] = urldecode($args_var_str);
			}
			else
			{
				// 兼容 __param-test 写法
				if (substr($args_var_str, 0, strlen($this->params['sep_var'])) == $this->params['sep_var'])
				{
					$args_var_str = substr($args_var_str, strlen($this->params['sep_var']));
				}
			
				if (!strstr($args_var_str, '-'))
				{
					$_GET['id'] = urldecode($args_var_str);
				}

				$uri['last'] = explode($this->params['sep_var'], $args_var_str);

				foreach ($uri['last'] as $val)
				{
					@list($k, $v) = explode($this->params['sep_value'], $val, 2);

					if ($k)
					{
						if (! strstr($v, '%'))
						{
							$_GET[$k] = $v;
						}
						else
						{
							$_GET[$k] = urldecode($v);
						}
					}
				}
			}
		}

		foreach ($_GET AS $key => $val)
		{
			if (strstr($key, '/'))
			{
				unset($_GET[$key]);
			}
		}
		
		return $this;
	}

}
