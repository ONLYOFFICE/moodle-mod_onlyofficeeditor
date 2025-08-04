<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'onlyofficeeditor', language 'zh_cn'.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['banner_description'] = '无需下载和安装即可轻松启动云端编辑器';
$string['banner_link_title'] = '立即获取';
$string['banner_title'] = 'ONLYOFFICE 文档云';
$string['checkdocserverbutton'] = '检查文档连接';
$string['connectionerror'] = '连接错误';
$string['connectionerror:command'] = '尝试检查 CommandService 时出错';
$string['connectionerror:convert'] = '尝试检查 ConvertService 时出错';
$string['connectionerror:unexpected'] = '检查连接时发生意外错误';
$string['connectionsuccess'] = '连接稳定';
$string['disable_verify_ssl'] = '关闭证书校验(不安全)';
$string['disable_verify_ssl:description'] = '仅在使用自签名证书访问文档服务器时使用';
$string['docserverunreachable'] = 'ONLYOFFICE文档服务器无法访问。请联系管理员';
$string['documentpermissions'] = '文件权限';
$string['documentservererror'] = '无法连接到 ONLYOFFICE 文档。请检查服务器是否正在运行且可访问。';
$string['documentserverinternal'] = '服务器内部请求 ONLYOFFICE Docs 的地址';
$string['documentserverinternal:description'] = '仅在 Moodle 服务器与 ONLYOFFICE 文档之间使用内部路由且不依赖公网地址时填写';
$string['documentserversecret'] = '文件服务器秘密';
$string['documentserversecret_desc'] = '该秘密在浏览器中生成令牌（加密签名），用于文档编辑器的打开和调用对文档命令服务和文档转换服务的方法和请求。该令牌可以防止ONLYOFFICE文档服务器请求中的重要参数替换。';
$string['documentserverurl'] = '文档编辑的服务地址';
$string['documentserverurl_desc'] = '文档编辑服务地址指定了带有文档服务的服务器地址。请用正确的服务器地址替换上面的 \'https://documentserver.url\'';
$string['docxformname'] = '文档';
$string['download'] = '文件可下载';
$string['download_help'] = '如果该功能被禁用，文件将不能在ONLYOFFICE编辑器应用程序中下载。请注意，具有<strong>course:manageactivities</strong>能力的用户总是能够通过应用程序下载文件';
$string['editor_security'] = '安全';
$string['editor_security_macros'] = '运行文档宏';
$string['editor_security_plugin'] = '启用插件';
$string['editor_view'] = '编辑器自定义设置';
$string['editor_view_chat'] = '显示聊天菜单按钮';
$string['editor_view_description'] = '了解如何自定义编辑器，请点击<a href="{$a->url}" target="_blank">此处</a>。';
$string['editor_view_feedback'] = '显示反馈及支持菜单按钮';
$string['editor_view_header'] = '更紧致地显示菜单栏';
$string['editor_view_help'] = '显示帮助菜单按钮';
$string['editor_view_toolbar'] = '显示单色工具栏标题';
$string['editorenterfullscreen'] = '打开全屏';
$string['editorexitfullscreen'] = '退出全屏';
$string['forcesave'] = '启用强制保存';
$string['jwtheader'] = '授权标头';
$string['mentioncontexturlname'] = '评论的链接';
$string['mentionnotifier:notification'] = '{$a->notifier}在{$a->course}被提及。';
$string['messageprovider:mentionnotifier'] = '在模块文件中 ONLYOFFICE 提及通知。';
$string['modulename'] = 'ONLYOFFICE 文档';
$string['modulename_help'] = 'ONLYOFFICE模块让用户能够使用ONLYOFFICE文档服务器创建并编辑存储在Moodle本地的办公文件，允许多个用户实时协作，并将所做的更改保存到Moodle。

想要了解更多，请访问 <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">帮助中心</a>.';
$string['modulenameplural'] = 'ONLYOFFICE 文档';
$string['oldversion'] = '请将ONLYOFFICE Docs更新到7.0版本，以便在线编辑可填写的表单。';
$string['onlyofficeactivityicon'] = '用 ONLYOFFICE 打开';
$string['onlyofficeeditor:addinstance'] = '添加新的ONLYOFFICE文档活动';
$string['onlyofficeeditor:editdocument'] = '编辑ONLYOFFICE文档活动';
$string['onlyofficeeditor:view'] = '查看ONLYOFFICE文档活动';
$string['onlyofficename'] = '活动名称';
$string['onmentionerror'] = '提及错误';
$string['pdfformname'] = 'PDF 表单';
$string['pluginadministration'] = 'ONLYOFFICE文档活动管理';
$string['pluginname'] = 'ONLYOFFICE 文档';
$string['pptxformname'] = '演示文稿';
$string['print'] = '文件可打印';
$string['print_help'] = '如果该功能被禁用，文件将不能在ONLYOFFICE编辑器应用程序中打印。请注意，具有<strong>course:manageactivities</strong> 能力的用户总是能够通过应用程序打印文件';
$string['privacy:metadata'] = '没有储存任何有关用户个人数据的信息。';
$string['privacy:metadata:onlyofficeeditor'] = '关于用ONLYOFFICE编辑的文件信息。';
$string['privacy:metadata:onlyofficeeditor:core_files'] = 'ONLYOFFICE文档活动存储已被编辑的文档。';
$string['privacy:metadata:onlyofficeeditor:course'] = 'ONLYOFFICE活动属于的课程。';
$string['privacy:metadata:onlyofficeeditor:intro'] = 'ONLYOFFICE活动的总体介绍';
$string['privacy:metadata:onlyofficeeditor:introformat'] = '介绍字段的格式（MOODLE, HTML, MARKDOWN...）。';
$string['privacy:metadata:onlyofficeeditor:name'] = 'ONLYOFFICE活动名称。';
$string['privacy:metadata:onlyofficeeditor:permissions'] = '文件权限。';
$string['privacy:metadata:onlyofficeeditor:userid'] = '实际用户ID没有发送到ONLYOFFICE编辑器。';
$string['protect'] = '隐藏保护选项卡';
$string['protect_help'] = '如果该功能被禁用，用户可以在ONLYOFFICE编辑器中访问保护设置。请注意，具有<strong>course:manageactivities</strong> 能力的用户总是能够访问保护设置。';
$string['readmore'] = '了解更多';
$string['returntodocument'] = '返回到课程页面';
$string['saveasbutton'] = '选择';
$string['saveaserror'] = '出了问题。';
$string['saveassuccess'] = '文件已保存成功。';
$string['saveastitle'] = '选择课程部分来保存文件';
$string['savewarning'] = '别忘了在页面底部点击';
$string['selectfile'] = '选择现有文件或通过点击其中一个图标创建新文件';
$string['settingsintro'] = '直接在 Moodle 课程结构中在线编辑和协作处理 Office 文档。可共享文档以供查看或实时协作编辑。创建、共享学生作业并为其进行评分。';
$string['storageurl'] = 'ONLYOFFICE Docs 内部请求服务器的地址';
$string['suggestfeature'] = '建议新功能';
$string['unsupportedfileformat'] = '文件格式不受支持。';
$string['uploadformname'] = '上传文件';
$string['validationerror:apijsunavailable'] = '无法获取 API JavaScript 文件。';
$string['validationerror:docsinvalidurl'] = '文档服务器 URL 无效。请检查 URL 格式。';
$string['validationerror:documentserverunreachable'] = '无法连接到 ONLYOFFICE 文档。请检查服务器是否正在运行且可访问。';
$string['validationerror:emptyurl'] = 'ONLYOFFICE 文档 URL 不能为空。';
$string['validationerror:incorrectjwtheader'] = '无法连接到 ONLYOFFICE 文档。请检查 Authorization 请求头是否正确。';
$string['validationerror:incorrectsecret'] = '无法连接到 ONLYOFFICE 文档。请检查密钥是否正确。';
$string['validationerror:invalidurl'] = 'URL 无效。请检查 URL 格式。';
$string['validationerror:mixedcontent'] = '不允许混合活动内容。ONLYOFFICE 文档需要 HTTPS 地址。';
$string['xlsxformname'] = '电子表格';
