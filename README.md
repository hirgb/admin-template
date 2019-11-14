# 智慧识屏运营平台

## 版本迭代

### v2.1.0
- 添加：ES管理工具

### v2.0.1
- 测试界面将当前测试term标红

### v2.0.0
- 发布2.0.0

### v1.1.9
- 完善：标注工作流程

### v1.1.8
- 修复：添加term后不能更新term_do表
- 更改：termHot中将百度搜索作为默认展示

### v1.1.7
- 添加：日常线上巡检任务功能
- 添加：后台脚本运行状态指示
- 添加：标记功能
- 添加：驳回功能
- 添加：干预历史展示
- 更改：页面菜单层级结构

### v1.1.6
- 界面细节修改
- 任务存在性检验

### v1.1.5
- 集成helper
- 增加去歧模型支持
- 修改“初始化任务”逻辑为“添加数据”逻辑
- redis干预历史记录中时间由12小时制改为24小时制
- 增加历史记录数据

### v1.1.4
- 修复了判断term是否存在的错误

### v1.1.3
- redis快速干预的restoreCache接口，由遍历全部历史改为遍历每个query的最后一次修改历史

### v1.1.2
- 按照原型2.0修改管理员创建任务、添加数据、删除任务等逻辑
- 添加创建任务时的表单验证

### v1.1.1
- 修改redis干预卡片高度
- 修改干预历史单页默认展现数量

### v1.1.0
- redis干预测试版上线

### v1.0.6
- 添加历史详情中意图的显示
- 更改数据库结构，支持emoji表情

### v1.0.5
- 解决数据表中segmentation字段不能写入的问题

### v1.0.4
- redis干预UI修改

### v1.0.3
- 发布历史页面添加加载更多按钮
- 添加redis快速干预功能
- 规范菜单名称

### v1.0.2
- 添加自定义实体词的去重
- 添加实体之后自动刷新页面
- 添加自定义实体页面删除时至少留一条实体
- 支持删除未初始化的任务
- 控制台打印当前版本

### v1.0.1
- 手动修改或微调一个标注item
- 手工添加term
- 支持多个维度一起筛选term
- ner筛选项，支持多选
- 展示已驳回的term
- 对已驳回的term提供修改功能
- 修复【ner筛选项，选中一个之后，无法回到全部ner】

### v1.0
- 简单用户系统
- 标注任务流程管理
- 内容活动管理
- 标注、测试、发布等操作

## Project setup
```
yarn install
```

### Compiles and hot-reloads for development
```
yarn run serve
```

### Compiles and minifies for production
```
yarn run build
```

### Run your tests
```
yarn run test
```

### Lints and fixes files
```
yarn run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
