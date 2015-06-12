# TODO we don't really need a userfunc here
includeLibs.FElogin = typo3conf/ext/tmpl_digizeit/Resources/Private/Scripts/FElogin.php
temp.felogin = USER_INT
temp.felogin {
	userFunc = user_FElogin->main
	storagePid = {$LICENSEUSER}
}

page.10.marks.FELOGIN < temp.felogin
lib.forgotPasswordPageUid = TEXT
lib.forgotPasswordPageUid.value = {$felogin.forgotPasswordPageUid}
