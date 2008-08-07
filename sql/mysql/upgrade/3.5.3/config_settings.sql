--
-- Table structure for table `config_groups`
--

CREATE TABLE `config_groups` (
  `id` int(255) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255),
  `description` mediumtext,
  `category` varchar(255),
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config_groups`
--

INSERT INTO `config_groups` VALUES
(1, 'browse', 'Browse View', 'Browse view configuration', 'User Interface Settings'),
(2, 'cache', 'Cache', 'KnowledgeTree cache configuration (Advanced users only)', 'General Settings'),
(3, 'CustomErrorMessages', 'Custom Error Messages', 'Custom error message screen configuration (Advanced users only)', 'General Settings'),
(4, 'dashboard', 'Dashboard', 'Dashboard configuration', 'General Settings'),
(5, 'DiskUsage', 'Disk Usage Dashlet', 'Disk usage dashlet configuration', 'General Settings'),
(6, 'email', 'Email', 'System email configuration. Note that these setting are required by a number of features.', 'Email Settings'),
(7, 'export', 'Export', 'Export configuration', 'General Settings'),
(8, 'externalBinary', 'External Binaries', 'Paths to external binaries used by KnowledgeTree (Advanced users only)', 'General Settings'),
(9, 'i18n', 'Internationalization', 'Internationalization configuration', 'Internationalisation Settings'),
(10, 'import', 'Import', 'Import configuration.', 'Internationalisation Settings'),
(11, 'indexer', 'Document Indexer', 'Document indexer configuration (Advanced users only)', 'Search and Indexing Settings'),
(12, 'KnowledgeTree', 'KnowledgeTree', 'General server configuration', 'General Settings'),
(13, 'KTWebDAVSettings', 'WebDAV', 'Third-party WebDAV configuration', 'Client Tools Settings'),
(14, 'openoffice', 'OpenOffice.org Service', 'OpenOffice.org service configuration. Note that this service is used by a number of features within KnowledgeTree', 'Client Tools Settings'),
(15, 'search', 'Search', 'Search configuration.', 'Search and Indexing Settings'),
(16, 'session', 'Session Management', 'Session management configuration.', 'General Settings'),
(17, 'storage', 'Storage', 'KnowledgeTree Storage Manager configuration', 'General Settings'),
(18, 'tweaks', 'Tweaks', 'Small configuration tweaks', 'General Settings'),
(19, 'ui', 'User Interface', 'General user interface configuration', 'User Interface Settings'),
(20, 'urls', 'Urls', 'KnowledgeTree server and filesystem paths (Advanced users only).', 'User Interface Settings'),
(21, 'user_prefs', 'User Preferences', 'User interface preferences', 'User Interface Settings'),
(22, 'webservice', 'Web Services', 'KnowledgeTree Web Service Interface configuration. Note that a number of KnowledgeTree Tools rely on this service. ', 'Client Tools Settings'),
(23, 'ldapAuthentication', 'LDAP Authentication', 'Configuration of the ldap authentication.', 'General Settings');

-- --------------------------------------------------------

--
-- Table structure for table `config_settings`
--

CREATE TABLE `config_settings` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL,
  `display_name` varchar(255),
  `description` mediumtext,
  `item` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL default 'default',
  `default_value` varchar(255) NOT NULL,
  `type` enum('boolean','string','numeric_string','numeric','radio','dropdown') default 'string',
  `options` mediumtext,
  `can_edit` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config_settings`
--

INSERT INTO `config_settings` VALUES
(1, 'ui', 'OEM Application Name', 'For use by KnowledgeTree OEM partners.', 'appName', 'KnowledgeTree', 'KnowledgeTree', '', NULL, 1),
(2, 'KnowledgeTree', 'Scheduler Interval', 'Set the frequency of the core scheduler.', 'schedulerInterval', 'default', '30', 'numeric_string', NULL, 1),
(3, 'dashboard', 'alwaysShowYCOD', 'Display the "Your Checked-out Documents" dashlet even when empty.', 'alwaysShowYCOD', 'default', 'false', 'boolean', NULL, 1),
(4, 'urls', 'Graphics Url', 'Path to user interface graphics', 'graphicsUrl', 'default', '${rootUrl}/graphics', '', NULL, 1),
(5, 'urls', 'User Interface Url', 'Path to core user interface libraries', 'uiUrl', 'default', '${rootUrl}/presentation/lookAndFeel/knowledgeTree', '', NULL, 1),
(6, 'tweaks', 'Browse to unit folder', 'Whether to browse to the user''s (first) unit when first going to the browse section.', 'browseToUnitFolder', 'default', 'false', 'boolean', NULL, 1),
(7, 'tweaks', 'Generic Metadata Required', '', 'genericMetaDataRequired', 'default', 'true', 'boolean', NULL, 1),
(8, 'tweaks', 'Noisy Bulk Operations', 'Whether bulk operations should generate a transaction notice on each ; item, or only on the folder.  Default of "false" indicates that only the folder transaction should occur.', 'noisyBulkOperations', 'default', 'false', 'boolean', NULL, 1),
(9, 'tweaks', 'Php Error Log File', 'If you want to enable PHP error logging to the log/php_error_log file, change this setting to true.', 'phpErrorLogFile', 'default', 'false', 'boolean', NULL, 1),
(10, 'email', 'Email Server', 'Address to SMTP server. Try IP address if host name does not work.', 'emailServer', 'none', 'none', '', NULL, 1),
(11, 'email', 'Email Port', 'SMTP server port', 'emailPort', 'default', '', 'numeric_string', NULL, 1),
(12, 'email', 'Email Authentication', 'Do you need auth to connect to SMTP?', 'emailAuthentication', 'default', 'false', 'boolean', NULL, 1),
(13, 'email', 'Email Username', 'Email server username', 'emailUsername', 'default', 'username', '', NULL, 1),
(14, 'email', 'Email Password', 'Email server password', 'emailPassword', 'default', 'password', '', NULL, 1),
(15, 'email', 'Email From', 'Email address from which system emails will be sent', 'emailFrom', 'default', 'kt@example.org', '', NULL, 1),
(16, 'email', 'Email From Name', 'Email from name', 'emailFromName', 'default', 'KnowledgeTree Document Management System', '', NULL, 1),
(17, 'email', 'Allow Attachment', 'Set to true to allow users to send attachments from the document management system.', 'allowAttachment', 'default', 'false', 'boolean', NULL, 1),
(18, 'email', 'Allow External Email Addresses', 'Set to true to allow users to send to any email address, as opposed to only users of the system.', 'allowEmailAddresses', 'default', 'false', 'boolean', NULL, 1),
(19, 'email', 'Send As System', 'Set to true to always send email from the emailFrom address listed above, even if there is an identifiable sending user.', 'sendAsSystem', 'default', 'false', 'boolean', NULL, 1),
(20, 'email', 'Only Own Groups', 'Set to true to only allow users to send emails to those in the same groups as them.', 'onlyOwnGroups', 'default', 'false', 'boolean', NULL, 1),
(21, 'user_prefs', 'Password Length', 'Minimum password length on password-setting', 'passwordLength', 'default', '6', 'numeric_string', NULL, 1),
(22, 'user_prefs', 'Restrict Admin Passwords', 'Apply the minimum password length to admin while creating / editing accounts? default is set to "false" meaning that admins can create users with shorter passwords.', 'restrictAdminPasswords', 'default', 'false', 'boolean', NULL, 1),
(23, 'user_prefs', 'Restrict Preferences', 'Restrict users from accessing their preferences menus?', 'restrictPreferences', 'default', 'false', 'boolean', NULL, 1),
(24, 'session', 'Session Timeout', 'Session timeout (in seconds)', 'sessionTimeout', 'default', '1200', 'numeric_string', NULL, 1),
(25, 'session', 'Anonymous Login', 'By default, do not auto-login users as anonymous. Set this to true if you UNDERSTAND the security system that KT uses, and have sensibly applied the roles "Everyone" and "Authenticated Users".', 'allowAnonymousLogin', 'default', 'false', 'boolean', NULL, 1),
(26, 'ui', 'Company Logo', 'Add the logo of your company to the site''s appearance. This logo MUST be 50px tall, and on a white background.', 'companyLogo', 'default', '${rootUrl}/resources/companylogo.png', '', NULL, 1),
(27, 'ui', 'Company Logo Width', 'The logo''s width in pixels', 'companyLogoWidth', 'default', '313px', '', NULL, 1),
(28, 'ui', 'Company Logo Title', 'ALT text - for accessibility purposes.', 'companyLogoTitle', 'default', 'ACME Corporation', '', NULL, 1),
(29, 'ui', 'Always Show All Results', 'Do not restrict to searches (e.g. always show_all) on users and groups pages.', 'alwaysShowAll', 'default', 'false', 'boolean', NULL, 1),
(30, 'ui', 'Condensed Admin UI', 'Use a condensed admin ui?', 'condensedAdminUI', 'default', 'false', 'boolean', NULL, 1),
(31, 'ui', 'Fake Mimetype', 'Allow "open" from downloads.  Changing this to "true" will prevent (most) browsers from giving users the "open" option.', 'fakeMimetype', 'default', 'false', 'boolean', NULL, 1),
(32, 'i18n', 'UseLike', 'If your language doesn''t have distinguishable words (usually, doesn''t have a space character), set useLike to true to use a search that can deal with this, but which is slower.', 'useLike', 'default', 'false', 'boolean', NULL, 1),
(33, 'import', 'unzip', 'Unzip command - will use execSearchPath to find if the path to the binary is not given.', 'unzip', 'default', 'unzip', '', NULL, 1),
(34, 'export', 'zip', 'Zip command - will use execSearchPath to find if the path to the binary is not given.', 'zip', 'default', 'zip', '', NULL, 1),
(35, 'externalBinary', 'xls2csv', 'Path to binary', 'xls2csv', 'default', 'xls2csv', '', NULL, 1),
(36, 'externalBinary', 'pdftotext', 'Path to binary', 'pdftotext', 'default', 'pdftotext', '', NULL, 1),
(37, 'externalBinary', 'catppt', 'Path to binary', 'catppt', 'default', 'catppt', '', NULL, 1),
(38, 'externalBinary', 'pstotext', 'Path to binary', 'pstotext', 'default', 'pstotext', '', NULL, 1),
(39, 'externalBinary', 'catdoc', 'Path to binary', 'catdoc', 'default', 'catdoc', '', NULL, 1),
(40, 'externalBinary', 'antiword', 'Path to binary', 'antiword', 'default', 'antiword', '', NULL, 1),
(41, 'externalBinary', 'python', 'Path to binary', 'python', 'default', 'python', '', NULL, 1),
(42, 'externalBinary', 'java', 'Path to binary', 'java', 'default', 'java', '', NULL, 1),
(43, 'externalBinary', 'php', 'Path to binary', 'php', 'default', 'php', '', NULL, 1),
(44, 'externalBinary', 'df', 'Path to binary', 'df', 'default', 'df', '', NULL, 1),
(45, 'cache', 'Proxy Cache Path', 'Path to KnowledgeTree Cache. Default is <var directory>/cache.', 'proxyCacheDirectory', 'default', '${varDirectory}/proxies', '', NULL, 1),
(46, 'cache', 'Proxy Cache Enabled', 'Enable cache. Note that the cache is disabled by default and enabling it may degrade performance.', 'proxyCacheEnabled', 'default', 'true', 'boolean', NULL, 1),
(47, 'KTWebDAVSettings', 'Debug', 'This section is for KTWebDAV  only, _LOTS_ of debug info will be logged if the following is "on"', 'debug', 'off', 'off', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(48, 'KTWebDAVSettings', 'Safemode', 'To allow write access to WebDAV clients set safe mode to "off".', 'safemode', 'on', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(49, 'search', 'Search Base Path', 'Path to search and indexing libraries', 'searchBasePath', 'default', '${fileSystemRoot}/search2', '', NULL, 1),
(50, 'search', 'Fields Path', 'Path to search and indexing fields', 'fieldsPath', 'default', '${searchBasePath}/search/fields', '', NULL, 1),
(51, 'search', 'Results Display Format', 'The format in which to display the results options are searchengine or browseview defaults to searchengine.', 'resultsDisplayFormat', 'default', 'searchengine', 'dropdown', 'a:1:{s:7:"options";a:2:{i:0;a:2:{s:5:"label";s:19:"Search Engine Style";s:5:"value";s:12:"searchengine";}i:1;a:2:{s:5:"label";s:17:"Browse View Style";s:5:"value";s:10:"browseview";}}}', 1),
(52, 'search', 'Results Per Page', 'The number of results per page, defaults to 25', 'resultsPerPage', 'default', '25', 'numeric_string', NULL, 1),
(53, 'search', 'Date Format', 'The date format used when making queries using widgets, defaults to Y-m-d', 'dateFormat', 'default', 'Y-m-d', '', NULL, 1),
(54, 'browse', 'Preview Activation', 'The document info box / preview is activated by mousing over or clicking on the icon. Options: mouse-over (default) or onclick.', 'previewActivation', 'default', 'onclick', 'dropdown', 'a:1:{s:7:"options";a:2:{i:0;a:2:{s:5:"label";s:9:"Mouseover";s:5:"value";s:10:"mouse-over";}i:1;a:2:{s:5:"label";s:8:"On Click";s:5:"value";s:7:"onclick";}}}', 1),
(55, 'indexer', 'Core Class', 'The core indexing class. Choices: JavaXMLRPCLuceneIndexer or PHPLuceneIndexer.', 'coreClass', 'default', 'JavaXMLRPCLuceneIndexer', '', NULL, 1),
(56, 'indexer', 'Batch Documents', 'The number of documents to be indexed in a cron session, defaults to 20.', 'batchDocuments', 'default', '20', 'numeric_string', 'a:3:{s:9:"increment";i:10;s:7:"minimum";i:20;s:7:"maximum";i:200;}', 1),
(57, 'indexer', 'Batch Migrate Documents', 'The number of documents to be migrated in a cron session, defaults to 500.', 'batchMigrateDocuments', 'default', '500', 'numeric_string', NULL, 1),
(58, 'indexer', 'Indexing Base Path', 'Path to indexing engine', 'indexingBasePath', 'default', '${searchBasePath}/indexing', '', NULL, 1),
(59, 'indexer', 'Lucene Directory', 'The location of the lucene indexes.', 'luceneDirectory', 'default', '${varDirectory}/indexes', '', NULL, 1),
(60, 'indexer', 'Extractor Path', 'Path to text extractors', 'extractorPath', 'default', '${indexingBasePath}/extractors', '', NULL, 1),
(61, 'indexer', 'Extractor Hook Path', 'Path to extractor hooks', 'extractorHookPath', 'default', '${indexingBasePath}/extractorHooks', '', NULL, 1),
(62, 'indexer', 'Java Lucene URL', 'The url for the Java Lucene Server. This should match up the the Lucene Server configuration. Defaults to http://127.0.0.1:8875', 'javaLuceneURL', 'default', 'http://127.0.0.1:8875', '', NULL, 1),
(63, 'openoffice', 'Host', 'The host on which open office is installed. Defaults to 127.0.0.1', 'host', 'default', '127.0.0.1', '', NULL, 1),
(64, 'openoffice', 'Port', 'The port on which open office is listening. Defaults to 8100', 'port', 'default', '8100', 'numeric_string', NULL, 1),
(65, 'webservice', 'Upload Directory', 'Directory to which all uploads via webservices are persisted before moving into the repository.', 'uploadDirectory', 'default', '${varDirectory}/uploads', '', NULL, 1),
(66, 'webservice', 'Download Url', 'Url which is sent to clients via web service calls so they can then download file via HTTP GET.', 'downloadUrl', 'default', '${rootUrl}/ktwebservice/download.php', '', NULL, 1),
(67, 'webservice', 'Upload Expiry', 'Period indicating how long a file should be retained in the uploads directory.', 'uploadExpiry', 'default', '30', 'numeric_string', 'a:1:{s:6:"append";s:7:"seconds";}', 1),
(68, 'webservice', 'Download Expiry', 'Period indicating how long a download link will be available.', 'downloadExpiry', 'default', '30', 'numeric_string', 'a:1:{s:6:"append";s:7:"seconds";}', 1),
(69, 'webservice', 'Random Key Text', 'Random text used to construct a hash. This can be customised on installations so there is less chance of overlap between installations.', 'randomKeyText', 'default', 'bkdfjhg23yskjdhf2iu', '', NULL, 1),
(70, 'webservice', 'Validate Session Count', 'Validating session counts can interfere with access. It is best to leave this disabled, unless very strict access is required.', 'validateSessionCount', 'false', 'false', 'boolean', NULL, 1),
(71, 'webservice', 'Use Default Document Type If Invalid', 'If the document type is invalid when adding a document, we can be tollerant and just default to the Default document type.', 'useDefaultDocumentTypeIfInvalid', 'true', 'true', 'boolean', NULL, 1),
(72, 'webservice', 'Debug', 'The web service debugging if the logLevel is set to DEBUG. We can set the value to 4 or 5 to get more verbose web service logging. Level 4 logs the name of functions being accessed. Level 5 logs the SOAP XML requests and responses.', 'debug', 'false', 'false', 'boolean', NULL, 1),
(73, 'DiskUsage', 'Warning Threshold', 'When free space in a mount point is less than this percentage, the disk usage dashlet will highlight the mount in ORANGE.', 'warningThreshold', '10', '10', 'numeric_string', 'a:1:{s:6:"append";s:1:"%";}', 1),
(74, 'DiskUsage', 'Urgent Threshold', 'When free space in a mount point is less than this percentage, the disk usage dashlet will highlight the mount in RED.', 'urgentThreshold', '5', '5', 'numeric_string', 'a:1:{s:6:"append";s:1:"%";}', 1),
(75, 'KnowledgeTree', 'Use AJAX Dashboard', 'User AJAX dashboard with rounded corners and draggable dashlets.', 'useNewDashboard', 'true', 'true', 'boolean', NULL, 1),
(76, 'i18n', 'Default Language', 'Default language for the interface.', 'defaultLanguage', 'default', 'en', 'string', NULL, 1),
(77, 'CustomErrorMessages', 'Custom Error Messages', 'Turn custom error messages on or off here', 'customerrormessages', 'default', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(78, 'CustomErrorMessages', 'Custom Error Page Path', 'Name or url of custom error page.', 'customerrorpagepath', 'default', 'customerrorpage.php', '', NULL, 1),
(79, 'CustomErrorMessages', 'Custom Error Handler', 'Turn custom error handler on or off', 'customerrorhandler', 'default', 'on', 'radio', 'a:1:{s:7:"options";a:2:{i:0;s:2:"on";i:1;s:3:"off";}}', 1),
(80, 'ui', 'Skinning Enabled', 'Enable Skinning', 'morphEnabled', 'default', 'false', 'boolean', NULL, 1),
(81, 'ui', 'Default Skin', 'Enter a default skin', 'morphTo', 'default', 'blue', '', NULL, 1),
(82, 'KnowledgeTree', 'Log Level', 'Choice: INFO or DEBUG', 'logLevel', 'default', 'INFO', 'dropdown', 'a:1:{s:7:"options";a:4:{i:0;a:2:{s:5:"label";s:4:"INFO";s:5:"value";s:4:"INFO";}i:1;a:2:{s:5:"label";s:4:"WARN";s:5:"value";s:4:"WARN";}i:2;a:2:{s:5:"label";s:5:"ERROR";s:5:"value";s:5:"ERROR";}i:3;a:2:{s:5:"label";s:5:"DEBUG";s:5:"value";s:5:"DEBUG";}}}', 1),
(83, 'storage', 'Manager', 'KnowledgeTree Storage manager. Default is KTOnDiskHashedStorageManager.', 'manager', 'default', 'KTOnDiskHashedStorageManager', '', NULL, 1),
(84, 'ui', 'ieGIF', 'Use the additional IE specific GIF theme overrides. Using this means that arbitrary theme packs may not work without having GIF versions available. ', 'ieGIF', 'false', 'true', 'boolean', NULL, 1),
(85, 'ui', 'Automatic Refresh', 'Set to true to automatically refresh the page after the session would have expired.', 'automaticRefresh', 'default', 'false', 'boolean', NULL, 1),
(86, 'ui', 'dot', 'Path to dot binary', 'dot', 'dot', 'dot', '', NULL, 1),
(87, 'urls', 'Log Directory', 'Path to log directory', 'logDirectory', 'default', '${varDirectory}/log', '', NULL, 1),
(88, 'urls', 'UI Directory', 'Path to UI directory', 'uiDirectory', 'default', '${fileSystemRoot}/presentation/lookAndFeel/knowledgeTree', '', NULL, 1),
(89, 'urls', 'Temp Directory', 'Path to temp directory', 'tmpDirectory', 'default', '${varDirectory}/tmp', '', NULL, 1),
(90, 'urls', 'Stopwords File', 'Path to stopword file', 'stopwordsFile', 'default', '${fileSystemRoot}/config/stopwords.txt', '', NULL, 1),
(91, 'cache', 'Cache Enabled', 'Plugin cache configuration', 'cacheEnabled', 'default', 'false', 'boolean', NULL, 1),
(92, 'cache', 'Cache Directory', 'Plugin cache path', 'cacheDirectory', 'default', '${varDirectory}/cache', '', NULL, 1),
(93, 'openoffice', 'Program Path', 'The Open Office program directory.', 'programPath', 'default', '../openoffice/program', 'string', NULL, 1),
(94, 'urls', 'documentRoot', '', 'documentRoot', 'default', '${varDirectory}/Documents', '', NULL, 0),
(95, 'KnowledgeTree', 'redirectToBrowse', 'set to true to redirect to browse screen ', 'redirectToBrowse', 'default', 'false', 'boolean', NULL, 1),
(96, 'KnowledgeTree', 'redirectToBrowseExceptions', 'if redirectToBrowse is true, adding usernames to this list will force specific users to be redirected to dashboard e.g. redirectToBrowseExceptions = admin, joebloggs ', 'redirectToBrowseExceptions', '', '', '', NULL, 1),
(97, 'session', 'Allow automatic sign in', 'If a user doesn''t exist in the system, the account will be created on first login.', 'allowAutoSignup', 'default', 'false', 'boolean', '', 1),
(98, 'ldapAuthentication', 'Automatic group creation', 'Automatically create the ldap groups.', 'autoGroupCreation', 'default', 'false', 'boolean', '', 1);
