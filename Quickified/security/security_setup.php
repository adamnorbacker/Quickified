<?php
defined('ABSPATH') or die('You are not allowed here.');

function htaccess_writetofile($htaccess, $nameforhtaccess, $lines)
{
    if (is_writable($htaccess)) {
        insert_with_markers($htaccess, $nameforhtaccess, $lines);
    } else {
        echo "<script>alert('Htaccess is not writeable at the moment, please make sure that htaccess is editable and then enter the admin area again.');</script>";
        logError("Htaccess is not writeable at the moment, please make sure that htaccess is editable and then enter the admin area again.");
    }
}

$isHttpsActivated = get_option('https-checkbox');
$serverSoftware = $_SERVER['SERVER_SOFTWARE'];
$htaccess = ABSPATH . ".htaccess";
$htaccess_admin = ABSPATH . "wp-admin/.htaccess";
$nameforhtaccess = "Quickified Security Main";
$lines = array();
$lines[] = "
<IfModule mod_rewrite.c>
Options +FollowSymlinks
RewriteEngine On
RewriteBase /
</IfModule>
#Disable directory browsing
IndexIgnore *
Options -Indexes
Options -MultiViews
ServerSignature Off
DefaultLanguage sv-SE
AddDefaultCharset utf-8

# Block wp-includes folder and files
<IfModule mod_rewrite.c>
RewriteRule ^wp-admin/includes/ - [F,L]
RewriteRule !^wp-includes/ - [S=3]
RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
RewriteRule ^wp-includes/theme-compat/ - [F,L]
</IfModule>

<IfModule mod_headers.c>
Header always unset 'X-Powered-By'
Header unset 'X-Powered-By'
Header set X-Permitted-Cross-Domain-Policies 'master-only'
Header always set Referrer-Policy 'strict-origin-when-cross-origin'
#Header set Content-Security-Policy: \"default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://www.googletagmanager.com https://www.google-analytics.com; media-src 'self'; object-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' https: data:; frame-src 'self'; worker-src 'self'; font-src 'self' data: https:;\"
Header set X-Frame-Options 'SAMEORIGIN'
<FilesMatch '\.(appcache|atom|bbaw|bmp|crx|css|cur|eot|f4[abpv]|flv|geojson|gif|htc|ic[os]|jpe?g|m?js|json(ld)?|m4[av]|manifest|map|markdown|md|mp4|oex|og[agv]|opus|otf|pdf|png|rdf|rss|safariextz|svgz?|swf|topojson|tt[cf]|txt|vcard|vcf|vtt|webapp|web[mp]|webmanifest|woff2?|xloc|xml|xpi)$'>
Header unset X-Frame-Options
</FilesMatch>
Header always set Strict-Transport-Security 'max-age=31536000; includeSubDomains; preload' env=HTTPS
header always set x-xss-protection '1; mode=block'
Header set X-Content-Type-Options 'nosniff'
Header edit Set-Cookie (.*) '$1; HTTPOnly; Secure; SameSite=strict' env=HTTPS
Header set Expect-CT 'max-age=5184000, enforce' env=HTTPS
</IfModule>";

if ($isHttpsActivated == 1) {
    if (stripos($serverSoftware, "apache") !== false) {
        $lines[] .=
            "
#Enforce HTTPS
<IfModule mod_rewrite.c>
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</IfModule>";
    } else if (stripos($serverSoftware, "nginx") !== false) {
        $lines[] .=
            "
#Enforce HTTPS
<IfModule mod_rewrite.c>
RewriteCond %{HTTP:X-Forwarded-SSL} on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</IfModule>";
    }
}
htaccess_writetofile($htaccess, $nameforhtaccess, $lines);

$nameforhtaccess = "Quickified Security Admin Area";
$lines = array();
$lines[] = "<IfModule mod_headers.c>
Header set Content-Security-Policy: \"default-src 'self' https://yoast.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://cdn.jsdelivr.net https://www.elegantthemes.com https://cdn.polyfill.io; media-src https: 'self'; object-src 'self'; style-src 'self' 'unsafe-inline' https:; img-src 'self' https: data:; frame-src 'self' https://www.elegantthemes.com; worker-src 'self'; font-src 'self' data: https:;\"
</ifmodule>
Header set Cache-Control 'no-cache, no-store, must-revalidate'
";
//Se ifall htaccess finns
if (file_exists($htaccess_admin)) {
    htaccess_writetofile($htaccess_admin, $nameforhtaccess, $lines);
} else {
    echo "<script>alert('Htaccess does not exist!');</script>";
    $createhtaccess = fopen($htaccess_admin, "w");
    if ($createhtaccess == true) {
        $closehandle = fclose($createhtaccess);
        if ($closehandle == true) {
            htaccess_writetofile($htaccess_admin, $nameforhtaccess, $lines);
        } else {
            echo "<script>alert('Cant close htaccess, contact support!');</script>";
        }
    } else {
        echo "<script>alert('Cant create htaccess, contact support!');</script>";
    }
}
$nameforhtaccess = "Quickified Security Firewall";
$lines = array();
$lines[] = "
# Quickified Blacklist 6G
# Deny domain access to spammers and other hackers

# 6G:[QUERY STRINGS]
<IfModule mod_rewrite.c>
RewriteCond %{QUERY_STRING} (author=\d+) [NC,OR]
RewriteCond %{QUERY_STRING} proc/self/environ [NC,OR]
RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|\%3D) [NC,OR]
RewriteCond %{QUERY_STRING} base64_encode.*(.*) [NC,OR]
RewriteCond %{QUERY_STRING} (eval\() [NC,OR]
RewriteCond %{QUERY_STRING} (127\.0\.0\.1) [NC,OR]
RewriteCond %{QUERY_STRING} ([a-z0-9]{2000,}) [NC,OR]
RewriteCond %{QUERY_STRING} (javascript:)(.*)(;) [NC,OR]
RewriteCond %{QUERY_STRING} (base64_encode)(.*)(\() [NC,OR]
RewriteCond %{QUERY_STRING} (GLOBALS|REQUEST)(=|\[|%) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)(.*)script(.*)(>|%3) [NC,OR]
RewriteCond %{QUERY_STRING} (\\\\|\.\.\.|\.\./|~|`|<|>|\|) [NC,OR]
RewriteCond %{QUERY_STRING} (boot\.ini|etc/passwd|self/environ) [NC,OR]
RewriteCond %{QUERY_STRING} (thumbs?(_editor|open)?|tim(thumb)?)\.php [NC,OR]
RewriteCond %{QUERY_STRING} (\'|\\\")(.*)(drop|insert|md5|select|union) [NC,OR]
RewriteCond %{QUERY_STRING} revslider [NC]
RewriteRule .* - [F]
</IfModule>

# 6G:[REQUEST URI]
<IfModule mod_rewrite.c>
RewriteCond %{REQUEST_URI} proc/self/environ [NC,OR]
RewriteCond %{REQUEST_URI} revslider [NC]
RewriteRule .* - [F]
</IfModule>

# 6G:[REQUEST METHOD]
<IfModule mod_rewrite.c>
RewriteCond %{REQUEST_METHOD} ^(connect|debug|move|put|trace|track) [NC]
RewriteRule .* - [F]
</IfModule>

# 6G:[REFERRERS]
<IfModule mod_rewrite.c>
RewriteCond %{HTTP_REFERER} ([a-z0-9]{2000,}) [NC,OR]
RewriteCond %{HTTP_REFERER} (semalt.com|todaperfeita) [NC]
RewriteRule .* - [F]
</IfModule>


# 6G:[REQUEST STRINGS]
<IfModule mod_alias.c>
RedirectMatch 403 (?i)([a-z0-9]{2000,})
RedirectMatch 403 (?i)(https?|ftp|php):/
RedirectMatch 403 (?i)(base64_encode)(.*)(\()
RedirectMatch 403 (?i)(=\\\\\'|=\\\\%27|/\\\\\'/?)\.
RedirectMatch 403 (?i)/(\\\$(\&)?|\*|\\\"|\.|,|&|&amp;?)/?$
RedirectMatch 403 (?i)(\{0\}|\(/\(|\.\.\.|\+\+\+|\\\\\\\"\\\\\\\")
#RedirectMatch 403 (?i)(~|`|<|>|:|;|,|%|\\\\|\s|\{|\}|\[|\]|\|)
RedirectMatch 403 (?i)/(=|\\\$&|_mm|cgi-|etc/passwd|muieblack)
RedirectMatch 403 (?i)(&pws=0|_vti_|\(null\)|\{\\\$itemURL\}|echo(.*)kae|etc/passwd|eval\(|self/environ)
RedirectMatch 403 (?i)\.(aspx?|bash|bak?|cfg|cgi|dll|exe|git|hg|ini|jsp|log|mdb|out|sql|svn|swp|tar|rar|rdf)$
RedirectMatch 403 (?i)/(^$|(wp-)?config|error_log|mobiquo|phpinfo|shell|sqlpatch|thumb|thumb_editor|thumbopen|timthumb|webshell)\.php
</IfModule>

# 6G:[USER AGENTS]
<IfModule mod_setenvif.c>
SetEnvIfNoCase User-Agent ([a-z0-9]{2000,}) bad_bot
SetEnvIfNoCase User-Agent (archive.org|binlar|casper|checkpriv|choppy|clshttp|cmsworld|diavol|dotbot|extract|feedfinder|flicky|g00g1e|harvest|heritrix|httrack|kmccrew|loader|miner|nikto|nutch|planetwork|postrank|purebot|pycurl|python|seekerspider|siclab|skygrid|sqlmap|sucker|turnit|vikspider|winhttp|xxxyy|youda|zmeu|zune|libwww-perl) bad_bot

# Apache < 2.3
<IfModule !mod_authz_core.c>
Order Allow,Deny
Allow from all
Deny from env=bad_bot
</IfModule>

# Apache >= 2.3
<IfModule mod_authz_core.c>
<RequireAll>
    Require all Granted
    Require not env bad_bot
</RequireAll>
</IfModule>
</IfModule>

<FilesMatch '(^#.*#|\.(bak|conf|dist|fla|in[ci]|log|psd|sh|sql|sw[op])|~)$'>

# Apache < 2.3
<IfModule !mod_authz_core.c>
Order allow,deny
Deny from all
Satisfy All
</IfModule>

# Apache ≥ 2.3
<IfModule mod_authz_core.c>
Require all denied
</IfModule>

</FilesMatch>


# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Internet Explorer Optimizations
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
<IfModule mod_headers.c>

Header set X-UA-Compatible \"IE=edge\"

<FilesMatch \"\.(appcache|atom|bbaw|bmp|crx|css|cur|eot|f4[abpv]|flv|geojson|gif|htc|ico|jpe?g|js|json(ld)?|m4[av]|manifest|map|mp4|oex|og[agv]|opus|otf|pdf|png|rdf|rss|safariextz|svgz?|swf|topojson|tt[cf]|txt|vcard|vcf|vtt|webapp|web[mp]|webmanifest|woff2?|xloc|xml|xpi)\$\">
Header unset X-UA-Compatible
</FilesMatch>

</IfModule>

<IfModule mod_headers.c>
Header set P3P \"policyref=\\\"/w3c/p3p.xml\\\", CP=\\\"IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\\\"\"
</IfModule>
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
# Block bad scans
# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
<IfModule mod_rewrite.c>
RewriteCond %{QUERY_STRING} revslider [NC,OR]
RewriteCond %{REQUEST_URI} revslider [NC]
RewriteRule .* - [F,L]
</IfModule>

<IfModule mod_rewrite.c>
RewriteCond %{QUERY_STRING} http\:\/\/www\.google\.com\/humans\.txt\? [NC]
RewriteRule .* - [F,L]
</IfModule>";
htaccess_writetofile($htaccess, $nameforhtaccess, $lines);

$nameforhtaccess = "Quickified Performance";
$lines = array();
$lines[] = "



<IfModule mod_deflate.c>
# Force compression for mangled headers.
# https://developer.yahoo.com/blogs/ydn/pushing-beyond-gzipping-25601.html

<IfModule mod_setenvif.c>
<IfModule mod_headers.c>
    SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
    RequestHeader append Accept-Encoding 'gzip,deflate' env=HAVE_Accept-Encoding
    # Don’t compress images and other uncompressible content
SetEnvIfNoCase Request_URI \
\.(?:gif|jpe?g|png|rar|zip|exe|flv|mov|wma|mp3|avi|swf|mp?g|mp4|webm|webp|woff|woff2)$ no-gzip dont-vary
</IfModule>
</IfModule>

#  Prevent Apache from recompressing them

<IfModule mod_mime.c>
AddEncoding gzip              svgz
</IfModule>

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

# Compress all output labeled with one of the following media types.

<IfModule mod_filter.c>
AddOutputFilterByType DEFLATE 'application/atom+xml' \
                              'application/javascript' \
                              'application/json' \
                              'application/ld+json' \
                              'application/manifest+json' \
                              'application/rdf+xml' \
                              'application/rss+xml' \
                              'application/schema+json' \
                              'application/vnd.geo+json' \
                              'application/vnd.ms-fontobject' \
                              'application/x-font-ttf' \
                              'application/x-javascript' \
                              'application/x-web-app-manifest+json' \
                              'application/xhtml+xml' \
                              'application/xml' \
                              'font/eot' \
                              'font/opentype' \
                              'font/truetype' \
                              'font/ttf' \
                              'image/bmp' \
                              'image/svg+xml' \
                              'image/vnd.microsoft.icon' \
                              'image/x-icon' \
                              'text/cache-manifest' \
                              'text/css' \
                              'text/html' \
                              'text/javascript' \
                              'text/plain' \
                              'text/vcard' \
                              'text/vnd.rim.location.xloc' \
                              'text/vtt' \
                              'text/x-component' \
                              'text/x-cross-domain-policy' \
                              'text/xml'

</IfModule>

</IfModule>

# Serve gzip compressed if they exist and the client accepts gzip.
<IfModule mod_headers.c>
RewriteCond '%{HTTP:Accept-encoding}' 'gzip'
RewriteCond '%{REQUEST_FILENAME}\.gz' -s
RewriteRule '^(.*)\.css' '$1\.css\.gz' [QSA]

RewriteCond '%{HTTP:Accept-encoding}' 'gzip'
RewriteCond '%{REQUEST_FILENAME}\.gz' -s
RewriteRule '^(.*)\.js' '$1\.js\.gz' [QSA]


# Serve correct content types, and prevent mod_deflate double gzip.
RewriteRule '\.css\.gz$' '-' [T=text/css,E=no-gzip:1]
RewriteRule '\.js\.gz$' '-' [T=text/javascript,E=no-gzip:1]


<FilesMatch '(\.js\.gz|\.css\.gz)$'>
Header append Content-Encoding gzip
</FilesMatch>
</IfModule>

<IfModule mod_headers.c>
Header set Cache-Control 'max-age=604800, public'
Header merge Cache-Control 'no-transform'
</IfModule>

FileETag MTime Size
#  Why are correct MIME types important? Lägg till description senare på admin area
<IfModule mod_mime.c>
    #application
    AddType application/A2L .a2l
    AddType application/AML .aml
    AddType application/ATF .atf
    AddType application/ATFX .atfx
    AddType application/atom+xml .atom
    AddType application/atomcat+xml .atomcat
    AddType application/atomdeleted+xml .atomdeleted
    AddType application/atomsvc+xml .atomsvc
    AddType application/ATXML .atxml
    AddType application/auth-policy+xml .apxml
    AddType application/json .json .map .topojson
    AddType application/ld+json .jsonld
    AddType application/rss+xml .rss
    AddType application/vnd.geo+json .geojson
    AddType application/javascript .js .mjs
    AddType application/manifest+json .webmanifest
    AddType application/x-web-app-manifest+json .webapp
    AddType application/wasm .wasm
    AddType application/octet-stream .safariextz
    AddType application/x-bb-appworld .bbaw
    AddType application/x-chrome-extension .crx
    AddType application/x-opera-extension .oex
    AddType application/x-xpinstall .xpi
    AddType application/x-shockwave-flash .swf
    AddType application/pdf .pdf
    #text
    AddType text/css .css
    AddType text/html .html .htm
    AddType text/xml .xml
    AddType text/xsl .xsl
    AddType text/cache-manifest .appcache
    AddType text/x-component .htc
    AddType text/calendar .ics
    AddType text/markdown .markdown .md
    AddType text/vcard .vcard .vcf
    AddType text/vnd.rim.location.xloc .xloc
    AddType text/vtt .vtt
    AddType text/csv .csv
    #images
    AddType image/x-icon .ico
    AddType image/bmp .bmp
    AddType image/gif .gif
    AddType image/jpeg .jpg .jpeg
    AddType image/png .png
    AddType image/svg+xml .svg
    AddType image/tiff .tiff .tif
    AddType image/webp .webp
    AddType image/apng .apng
    #fonts
    AddType font/collection .ttc
    AddType font/sfnt .ttf .otf
    AddType font/ttf .ttf
    AddType font/eot .eot
    AddType font/otf .otf
    AddType font/woff .woff
    AddType font/woff .woff2
    #Videos
    AddType video/3gpp .3gp .3gpp
    AddType video/3gpp2 .3gp2 .3gpp2
    AddType video/iso.segment .m4s
    AddType video/mj2 .mj2 .mjp2
    AddType video/mp4 .f4v .f4p .m4v .mp4
    AddType video/ogg .ogv
    AddType video/vnd.dece.hd .uvh .uvvh
    AddType video/vnd.dece.mobile .uvm .uvvm
    AddType video/vnd.dece.pd .uvp .uvvp
    AddType video/vnd.dece.sd .uvs .uvvs
    AddType video/vnd.dece.video .uvv .uvvv
    AddType video/vnd.dvb.file .dvb
    AddType video/vnd.fvt .fvt
    AddType video/vnd.hns.video .rm
    AddType video/vnd.mpegurl .mxu .m4u
    AddType video/vnd.ms-playready.media.pyv .pyv
    AddType video/vnd.nokia.interleaved-multimedia .nim
    AddType video/vnd.radgamettools.bink .bik .bk2
    AddType video/vnd.radgamettools.smacker .smk
    AddType video/vnd.sealed.mpeg1 .smpg .s11
    AddType video/vnd.sealed.mpeg4 .smpg .s14
    AddType video/vnd.sealed.swf .sswf .ssw
    AddType video/vnd.sealedmedia.softseal.mov .smov .smo .s1q
    AddType video/vnd.uvvu.mp4 .uvu .uvvu
    AddType video/webm .webm
    AddType video/x-flv .flv
    # Don't let compressed svgz be compressed again
    AddEncoding gzip .svgz

    #Set AddCharset
        AddCharset UTF-8 .js .css .html .htm .xml .xsl .atom .bbaw .geojson .ics .json .jsonld .manifest .markdown .md .mjs .rdf .rss .rss .vtt .webapp .webmanifest .xloc
</IfModule>
<IfModule mod_expires.c>

ExpiresActive on
ExpiresDefault                                      'access plus 1 year'

# CSS
ExpiresByType text/css                              'access plus 1 year'
# Data interchange
ExpiresByType application/json                      'access plus 0 seconds'
ExpiresByType application/ld+json                   'access plus 0 seconds'
ExpiresByType application/schema+json               'access plus 0 seconds'
ExpiresByType application/vnd.geo+json              'access plus 0 seconds'
ExpiresByType application/xml                       'access plus 0 seconds'
ExpiresByType text/xml 								'access plus 0 seconds'

# Favicon (cannot be renamed!) and cursor images
ExpiresByType image/vnd.microsoft.icon 				'access plus 1 year'
ExpiresByType image/x-icon                          'access plus 1 year'

# HTML components (HTCs)
ExpiresByType text/x-component                      'access plus 1 month'

# HTML
ExpiresByType text/html                             'access plus 0 seconds'

# JavaScript
ExpiresByType application/javascript                'access plus 1 year'
ExpiresByType application/x-javascript              'access plus 1 year'
ExpiresByType text/javascript 						'access plus 1 year'

# Manifest files
ExpiresByType application/manifest+json             'access plus 1 month'
ExpiresByType application/x-web-app-manifest+json   'access plus 0 seconds'
ExpiresByType text/cache-manifest                   'access plus 0 seconds'

# Media
ExpiresByType audio/ogg                             'access plus 1 year'
ExpiresByType image/bmp                             'access plus 1 year'
ExpiresByType image/gif                             'access plus 1 year'
ExpiresByType image/jpeg                            'access plus 1 year'
ExpiresByType image/png                             'access plus 1 year'
ExpiresByType image/svg+xml                         'access plus 1 year'
ExpiresByType image/webp                            'access plus 1 year'
ExpiresByType video/mp4                             'access plus 1 year'
ExpiresByType video/ogg                             'access plus 1 year'
ExpiresByType video/webm 							'access plus 1 year'

# Web feeds
ExpiresByType application/atom+xml                  'access plus 6 hours'
ExpiresByType application/rdf+xml 					'access plus 6 hours'
ExpiresByType application/rss+xml                   'access plus 6 hours'

# Web fonts
ExpiresByType application/vnd.ms-fontobject         'access plus 1 year'
ExpiresByType font/eot                              'access plus 1 year'
ExpiresByType font/opentype                         'access plus 1 year'
ExpiresByType application/x-font-ttf                'access plus 1 year'
ExpiresByType application/font-woff                 'access plus 1 year'
ExpiresByType application/x-font-woff               'access plus 1 year'
ExpiresByType font/woff                             'access plus 1 year'
ExpiresByType application/font-woff2                'access plus 1 year'


# Other

ExpiresByType text/x-cross-domain-policy 'access plus 1 week'

</IfModule>

# Cache the following content for 1 month (4 Weeks)
<FilesMatch '\.(jpg|jpeg|png|gif|ico)$'>
Header set Cache-Control 'max-age=2419200, public'
</FilesMatch>

#Fix adminbar and dynamic content
<filesMatch '\.(html|htm|php)$'>
FileETag None
<ifModule mod_headers.c>
Header unset ETag
Header set Cache-Control 'max-age=0, no-cache, no-store, must-revalidate'
Header set Pragma 'no-cache'
Header set Expires 'Wed, 10 Jan 1923 05:00:00 GMT'
</ifModule>
</filesMatch>

#Disable caching for logged in users
SetEnvIf Cookie .*wordpress_logged_in_.* nologgedincache
Header set curry 'Logged In' env=nologgedincache
<ifModule mod_headers.c>
Header unset ETag env=nologgedincache
Header set Cache-Control 'max-age=0, no-cache, no-store, must-revalidate' env=nologgedincache
Header set Pragma 'no-cache' env=nologgedincache
Header set Expires 'Wed, 10 Jan 1923 05:00:00 GMT' env=nologgedincache
</ifModule>

<ifModule mod_rewrite.c>
  RewriteEngine On 
  RewriteCond %{HTTP_ACCEPT} image/webp
  RewriteCond %{REQUEST_URI}  (?i)(.*)(\.jpe?g|\.png)$ 
  RewriteCond %{DOCUMENT_ROOT}%1.webp -f
  RewriteRule (?i)(.*)(\.jpe?g|\.png)$ %1\.webp [L,T=image/webp,R] 
</IfModule>

<IfModule mod_headers.c>
  Header append Vary Accept-Encoding env=REDIRECT_accept
</IfModule>

";
htaccess_writetofile($htaccess, $nameforhtaccess, $lines);
