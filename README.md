## 1688-crawler，仅供学习参考使用
 &emsp;&emsp;使用php采集[阿里巴巴](https://www.1688.com)百万商户信息稳定版。该项目结合[phantomjs](http://phantomjs.org/)，无需浏览器的情况下进行快速的“Web浏览”，相当于人为去浏览网页从而更好的渲染页面，提高数据抓取的准确率。此外，经过长期测试，对反爬虫进行特殊处理，运行稳定。

## 环境配置：
- 安装php5.6、mysql和composer（自行google）

## 运行
- 用db.1688.init.sql文件创建数据库

- 在mysqli-open.php文件添加数据库信息

- 下载 [php-phantomjs-master](https://github.com/jonnnnyw/php-phantomjs)

- 解压缩运行composer install

- 将cache和Robots拷贝到php-phantomjs-master目录

- 进入项目根目录bank运行 php 1688.php


---------

##### 由于该网站url和部分标签经常更换，运行时可能需要修改正则表达式

# 特别申明：此项目仅供学习参考使用，禁止商业用途

---------


