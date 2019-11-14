/*
 * @Description: 配置后端服务器
 * @Author: 张克飞
 * @Date: 2019-08-09 15:48:20
 * @LastEditTime: 2019-09-11 17:16:10
 * @LastEditors: Please set LastEditors
 */

const env = 2

const envs = [
    'localhost',
    'onlineTest',
    'online',
]

const config = {
    localhost: {
        dataServer: "http://127.0.0.1:8901/",
        debug: true,
    },
    onlineTest: {
        dataServer: "http://10.0.4.117:10011/",
        debug: false,
    },
    online: {
        dataServer: "http://phone.bridge.sanjiaoshou.net/",
        debug: false,
    }
}


export default {
    ...config[envs[env]],
    everydayMissionName: '日常线上巡检',
    version: '2.1.0',
};
