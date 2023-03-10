import Login from './views/Login.vue'
import NotFound from './views/404.vue'
import Home from './views/Home.vue'
import Main from './views/Main.vue'
import Table from './views/nav1/Table.vue'
import Form from './views/nav1/Form.vue'
import course from './views/nav1/course.vue'
import user from './views/nav1/user.vue'
import Page4 from './views/nav2/Page4.vue'
import Page5 from './views/nav2/Page5.vue'
import Page6 from './views/nav3/Page6.vue'
import echarts from './views/charts/echarts.vue'
import courseMange from './views/course/courseMange.vue';
import expManage from './views/course/expManage.vue'
import exprement from './views/course/exprement.vue'

let routes = [
    {
        path: '/login',
        component: Login,
        name: '',
        hidden: true
    },
    {
        path: '/404',
        component: NotFound,
        name: '',
        hidden: true
    },
    //{ path: '/main', component: Main },
    {
        path: '/',
        component: Home,
        name: '学生信息',
        iconCls: 'fa fa-address-card-o',//图标样式class
        children: [
            { path: '/main', component: Main, name: '主页', hidden: true },
            { path: '/table', component: Table, name: '学生信息管理' },
            // { path: '/user', component: user, name: '列表' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '成绩管理',
        iconCls: 'fa fa fa-server',
        children: [
            { path: '/form', component: Form, name: '实验成绩管理' },
            { path: '/course', component: course, name: '课程成绩管理' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '实验课程',
        iconCls: 'fa fa fa-server',
        children: [
            // { path: '/course', component: courseMange, name: '实验体系' },
            { path: '/expManage', component: expManage, name: '课程管理' },
            { path: '/exprement', component: exprement, name: '实验项目管理' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '账号管理',
        iconCls: 'fa fa-id-card-o',
        children: [
            { path: '/page4', component: Page4, name: '修改密码' },
        ]
    },
    {
        path: '/',
        component: Home,
        name: '',
        iconCls: 'fa fa-address-card',
        leaf: true,//只有一个节点
        children: [
            { path: '/page6', component: Page6, name: '学期设置' }
        ]
    },
    {
        path: '/',
        component: Home,
        name: 'Charts',
        iconCls: 'fa fa-bar-chart',
        children: [
            { path: '/echarts', component: echarts, name: '统计' }
        ]
    },
    {
        path: '*',
        hidden: true,
        redirect: { path: '/404' }
    }
];

export default routes;