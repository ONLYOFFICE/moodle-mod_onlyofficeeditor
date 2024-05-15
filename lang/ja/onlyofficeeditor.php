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
 * Strings for component 'onlyofficeeditor', language 'ja'.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['editorenterfullscreen'] = '全画面表示で開く';
$string['editorexitfullscreen'] = '全画面表示の終了';
$string['onmentionerror'] = ' メンションのエラー';
$string['mentioncontexturlname'] = 'コメントへのリンク';
$string['messageprovider:mentionnotifier'] = '「文書」モジュール内のONLYOFFICEメンション通知';
$string['mentionnotifier:notification'] = '{$a->notifier} が{$a->course}でメンションされました';
$string['docxformname'] = 'ドキュメント';
$string['pptxformname'] = 'プレゼンテーション';
$string['xlsxformname'] = 'スプレッドシート';
$string['docxfformname'] = '';
$string['uploadformname'] = 'ファイルをアップロードする';
$string['modulename'] = 'ONLYOFFICEの文書';
$string['modulenameplural'] = 'ONLYOFFICEドキュメント';
$string['modulename_help'] = 'ONLYOFFICEモジュールは、ONLYOFFICE Document Serverを使用して、Moodleにローカルのオフィス文書を作成・編集することができ、複数のユーザーがリアルタイムで共同作業を行い、それらの変更をMoodleに保存することができます。';
$string['pluginname'] = 'ONLYOFFICEの文書';
$string['pluginadministration'] = 'ONLYOFFICE ドキュメント活動管理';
$string['onlyofficename'] = '活動名';

$string['onlyofficeactivityicon'] = 'ONLYOFFICEで開く';
$string['onlyofficeeditor:addinstance'] = '新しいONLYOFFICEドキュメントの活動を追加する';
$string['onlyofficeeditor:view'] = 'ONLYOFFICEドキュメントの活動を表示する';
$string['onlyofficeeditor:editdocument'] = 'ONLYOFFICEドキュメントの活動を編集する';

$string['documentserverurl'] = '文書編集サービスのアドレス';
$string['documentserverurl_desc'] = '文書編集サービスアドレスは、文書サービスがインストールされているサーバーのアドレ スを指定します。上の \'https://documentserver.url\' を正しいサーバーアドレスに置き換えてください。';
$string['documentserversecret'] = 'ドキュメントサーバーのシクレット';
$string['documentserversecret_desc'] = 'このシクレットは、ドキュメントエディタを開いてメソッドを呼び出したり、ドキュメントコマンドサービスやドキュメント変換サービスへのリクエストのために、ブラウザでトークン（暗号化された署名）を生成するために使われます。トークンはONLYOFFICE Document Serverリクエストの重要なパラメータを置換することを防止します。';
$string['jwtheader'] = '認証ヘッダー';
$string['disable_verify_ssl'] = '証明書の検証を無効にする（安全ではありません)';

$string['selectfile'] = 'アイコンをクリックして、既存のファイルを選択するか、新規ファイルを作成します。';
$string['documentpermissions'] = 'ドキュメントの権限';
$string['download'] = 'ドキュメントをダウンロードできます';
$string['download_help'] = 'このオプションがオフの場合、ONLYOFFICEエディタアプリで文書をダウンロードすることはできません。ご注意：<strong>course:manageactivities</strong> 権を持つユーザーは、常にアプリからドキュメントをダウンロードすることができます。';
$string['print'] = 'ドキュメントの印刷が可能になっています';
$string['print_help'] = 'このオプションがオフの場合、ONLYOFFICEエディタアプリからドキュメントを印刷することはできません。ご注意：<strong>course:manageactivities</strong> 権を持つユーザーは、常にアプリで文書を印刷することができます。';
$string['protect'] = '保護タブを隠す';
$string['protect_help'] = 'このオプションがオフの場合、ユーザーはONLYOFFICEエディタで保護設定にアクセスすることができます。なお、<strong>course:manageactivities</strong> 権を持つユーザーは、常に保護設定にアクセスすることができます。';

$string['returntodocument'] = 'コースページに戻る';
$string['docserverunreachable'] = 'ONLYOFFICE Document Server にアクセスできません。管理者にご連絡ください。';
$string['privacy:metadata'] = 'ユーザーの個人情報はサーバーに保存されません';
$string['privacy:metadata:onlyofficeeditor:userid'] = '実際のユーザーIDはONLYOFFICEエディタに送信されません';
$string['privacy:metadata:onlyofficeeditor:intro'] = 'ONLYOFFICE活動の全体紹介';
$string['privacy:metadata:onlyofficeeditor:introformat'] = 'イントロフィールドのフォーマット（MOODLE、HTML、MARKDOWN・・・）';
$string['privacy:metadata:onlyofficeeditor:permissions'] = 'ドキュメントの許可';
$string['privacy:metadata:onlyofficeeditor:name'] = 'ONLYOFFICEの活動名';
$string['privacy:metadata:onlyofficeeditor:course'] = 'ONLYOFFICEの活動が所属しているコース';
$string['privacy:metadata:onlyofficeeditor'] = 'ONLYOFFICEで編集した文書に関する情報';
$string['privacy:metadata:onlyofficeeditor:core_files'] = 'ONLYOFFICEのドキュメント活動は、編集されたドキュメントを保存します';
$string['forcesave'] = '強制保存を有効にする';
$string['editor_view'] = 'エディタのカスタム設定';
$string['editor_view_chat'] = '「チャットメニュー」ボタンを表示する';
$string['editor_view_help'] = '「ヘルプメニュー」ボタンを表示する';
$string['editor_view_header'] = 'ヘッダーをよりコンパクトに表示する';
$string['editor_view_feedback'] = 'フィードバックと「サポートメニュー」ボタンを表示する';
$string['editor_view_toolbar'] = '白黒のツールバーヘッダーを表示する';
$string['editor_security'] = 'セキュリティ';
$string['editor_security_plugin'] = 'プラグインを有効にする';
$string['editor_security_macros'] = 'ドキュメントマクロを実行する';
$string['banner_title'] = 'ONLYOFFICE Docs Cloud';
$string['banner_description'] = 'ダウンロードやインストールをすることなく、クラウド上で簡単にエディタを起動することができます';
$string['banner_link_title'] = '今すぐ使ってみる';

$string['oldversion'] = 'オンラインで記入式フォームを作成するには、ONLYOFFICE Docs 7.0版まで更新してください';
$string['saveaserror'] = 'エラーが発生しました';
$string['saveassuccess'] = 'ドキュメントは正常に保存されました';
$string['saveastitle'] = 'ドキュメントを保存するコースセクションを選択する';
$string['saveasbutton'] = '選択する';
