// url中不同的path下对应的(不同角色)不同的头部导航条的信息
export let menu = {
    // 活动管理
    activity: {
        admin: [{
                label: '全部',
                name: 'acivityAll'
            },
            {
                label: '未开始',
                name: 'acivityAwait'
            },
            {
                label: '进行中',
                name: 'acivityUnderway'
            },
            {
                label: '已结束',
                name: 'acivityDone'
            },
        ],
    },
    mission: {
        admin: [{
                label: '全部',
                name: 'missionAll'
            },
            {
                label: '进行中',
                name: 'missionUnderway'
            },
            {
                label: '已完成',
                name: 'missionDone'
            },
        ],
        marker: [{
                label: '全部',
                name: 'missionAll'
            },
            {
                label: '进行中',
                name: 'missionUnderway'
            },
            {
                label: '已完成',
                name: 'missionDone'
            },
        ],
        tester: [{
                label: '全部',
                name: 'missionAll'
            },
            {
                label: '进行中',
                name: 'missionUnderway'
            },
            {
                label: '已完成',
                name: 'missionDone'
            },
        ],
    },

    // path为term(实体)的页面中不同角色对应的不同导航条
    term: {
        admin: [{
                label: '热门榜单',
                name: 'termHot'
            },
            {
                label: '标注',
                name: 'termAll'
            },
            {
                label: '发布',
                name: 'termPublish'
            },
            {
                label: '我的驳回',
                name: 'termReject'
            },
            {
                label: '被标记',
                name: 'termMark'
            },
        ],
        marker: [{
                label: '热门榜单',
                name: 'termHot'
            },
            {
                label: '标注',
                name: 'termEdit'
            },
            {
                label: '测试',
                name: 'termTest'
            },
            {
                label: '我的标记',
                name: 'termMark'
            },
        ],
        tester: [{
                label: '待测试',
                name: 'termTest'
            },
            {
                label: '发布',
                name: 'termPublish'
            },
            {
                label: '我的驳回',
                name: 'termReject'
            },
            {
                label: '我的标记',
                name: 'termMark'
            },
        ],
    },
    'tools/esmanager': [{
            label: '数据管理',
            name: 'contentData'
        },
        {
            label: '修改历史',
            name: 'contentHistory'
        },
    ],
}
