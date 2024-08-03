# PHP-SQL

本项目旨在提供一个简单的数据存储与管理解决方案，适用于需要基本文件存储和访问功能的应用场景。

[![License](https://img.shields.io/badge/license-GPL--3.0-blue.svg)](LICENSE)

## 概述

本项目提供了基本的数据存储功能，支持数据的存储、覆盖存储、读取以及删除操作。它适合用于小型应用或者需要简单数据管理的场合。

## 功能特点

- **存储数据**：可以存储加密后的数据到指定的文件。
- **检索数据**：通过授权码和数据名称检索存储的数据。
- **删除数据**：删除存储的数据文件，需要有效的授权码进行验证。

## 技术栈

- PHP
- OpenSSL 加密算法

## 配置要求

- PHP 7.0 及以上版本
- 服务器支持文件操作和加密模块

## 安装和使用

### 安装

1.将文件放置在网站目录
2.修改授权码和加密秘钥

### 使用[POST请求]

#### 使用 POST 请求存储数据

`curl -X POST 'http://your-domain.com/database.php'
-H 'Content-Type: application/x-www-form-urlencoded'
--data-urlencode 'action=store'
--data-urlencode 'name=数据表'
--data-urlencode 'data=数据值'
--data-urlencode 'authorization_code=授权码'`

#### 使用 POST 请求覆盖已存在的数据

`curl -X POST 'http://your-domain.com/database.php'
-H 'Content-Type: application/x-www-form-urlencoded'
--data-urlencode 'action=store'
--data-urlencode 'name=cs'
--data-urlencode 'data=Updated Data'
--data-urlencode 'authorization_code=授权码'`

#### 使用 POST 请求读取的数据
`curl -X POST \
  -d "action=retrieve" \
  -d "name=数据表" \
  -d "authorization_code=访问码" \
  http://your-domain.com/database.php`


### 使用[GET请求]

#### 使用 GET 请求读取数据

`curl -X GET 'http://your-domain.com/database.php?action=retrieve&name=数据表&authorization_code=授权码' `

#### 使用 GET 请求删除数据

`curl -X GET 'http://your-domain.com/database.php?action=delete&name=数据表&authorization_code=授权码'`

#### 使用 GET 请求存储数据

`curl -X GET 'http://your-domain.com/database.php?action=store&name=数据表&data=数据值&authorization_code=授权码'`



