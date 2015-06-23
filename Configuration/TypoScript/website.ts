temp.maincaption = TEXT
temp.maincaption.field = subtitle // title
[globalVar = TSFE:id=239]
	temp.maincaption >
[global]

# Default PAGE object:
page = PAGE
page.typeNum = 0
page.config.metaCharset = utf-8
page.config.additionalHeaders = Content-Type:text/html;charset=utf-8

# FE-User to BE-User
page.headerData.10 < plugin.tx_simulatebe_pi1

# Meta tags
page.headerData.999 < plugin.meta

page.includeCSS {
	file1 = EXT:tmpl_digizeit/Resources/Public/Css/digizeit.css
	file1.rel = stylesheet
	file1.type = text/css
	file1.media = screen,projection
}

page.includeJSlibs {
	html5shiv = EXT:tmpl_digizeit/Resources/Public/JavaScript/html5shiv.min.js
	html5shiv.allWrap = <!--[if lt IE 9]>|<![endif]-->
	respond = EXT:tmpl_digizeit/Resources/Public/JavaScript/respond.min.js
	respond.allWrap = <!--[if lt IE 9]>|<![endif]-->
	script = EXT:tmpl_digizeit/Resources/Public/JavaScript/script.js
}

page.10 = FLUIDTEMPLATE
page.10 {
	template = FILE
	extbase.controllerExtensionName = tmpl_digizeit
	partialRootPath = EXT:tmpl_digizeit/Resources/Private/Partials/
	layoutRootPath = EXT:tmpl_digizeit/Resources/Private/Templates/Layouts/
	settings {
	}
	variables {
		language = TEXT
		language.data = TSFE:config|config|sys_language_uid
		language.insertData = 1

		maincaption < temp.maincaption
	}
}

# ALTERNATIVES SEITENLAYOUT
#  ***************************************************
[globalVar = TSFE:page|layout=0]
	page.10.template.file = EXT:tmpl_digizeit/Resources/Private/Templates/Main.html
[globalVar = TSFE:page|layout=1]
	page.10.template.file = EXT:tmpl_digizeit/Resources/Private/Templates/Full.html
[globalVar = TSFE:page|layout=2]
	page.10.template.file = EXT:tmpl_digizeit/Resources/Private/Templates/Search.html
[globalVar = TSFE:page|layout=3]
	page.10.template.file = EXT:tmpl_digizeit/Resources/Private/Templates/FullToc.html
[global]

page.10.marks.HLINE = TEXT
page.10.marks.HLINE.value =

page.10 {

	# Substitute the ###menu### subparts with dynamic menu
	subparts.menu < temp.menu

	# Substitute the ###menu### subparts with dynamic menu
	subparts.breadcrumb < temp.breadcrumb

	# Substitute the ###tabmenu### subparts with nothing
	subparts.submenu < temp.submenu

	# Substitute the ###maincontent### subpart :
	subparts.maincontent < styles.content.get
	#    subparts.maincontent < temp.maincontent

	# Substitute the ###maincaption### subpart:
	subparts.sidebar < styles.content.getRight

	# Substitute the ###footmenu### subparts with dynamic menu
	subparts.footmenu < temp.footmenu
}

[globalVar = TSFE:type=100]
	config.admPanel = 0
	xmlnews = PAGE
	xmlnews {
		typeNum = 100
		10 >
		10 < plugin.tt_news
		10.singlePid = 227
		10.backPid = 239
		10.defaultCode = XML
		config {
			disableAllHeaderCode = 1
			additionalHeaders = Content-type:text/xml
			xhtml_cleaning = 0
		}
	}
[end]

plugin.tx_femanager.view {
	templateRootPath = EXT:tmpl_digizeit/Resources/Private/Templates/Extensions/Femanager/Templates/
	partialRootPath = EXT:tmpl_digizeit/Resources/Private/Templates/Extensions/Femanager/Partials/
	layoutRootPath = EXT:tmpl_digizeit/Resources/Private/Templates/Extensions/Femanager/Layouts/
}
