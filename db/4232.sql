UPDATE core_setting 
SET 
param_value = 'exe,rar,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv'
WHERE
param_name = 'file_upload_whitelist' 
;