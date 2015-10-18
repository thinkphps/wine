/**
 * Created by Kevin on 15-1-22.
 */
var version = '20150508';

seajs.config({
    //基础路径
    base: '/Public/',
    // 别名配置
    alias: {

		//TSB common公共的js
        jquery      : 'static/jquery.min.js',
        main        : 'libs/main.js',
        T           : 'libs/TSB.js',
        Public      : 'Home/js/public.js',

        //首页
        Menudown    : 'static/js/menudown.js',
        Index       : 'Home/js/index/index.js',
        ToolBar     : 'Home/js/index/toolBar.js',
        Login       : 'Home/js/index/login.js',

        //详细内容页面
        Detail      : 'Home/js/detail/detail.js',

        //购物车
        Carlist     : 'Home/js/car/carlist.js',

        //后台登陆
        AdminLogin  : 'Admin/js/login/login.js'



    },
    // 预加载项
    preload: [
        "jquery"
    ],
    // 调试模式
    //debug: true,
    // 映射配置
    map: [
        [/^(.*\.(?:css|js))(?:.*)$/i, '$1?version=' + version]
    ],
    // 文件编码
    charset: 'utf-8'
});
