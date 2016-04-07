#include "/usr/fcgi-2.4.1/include/fcgi_stdio.h"
#include <stdlib.h>
#include <string.h>
#include <dlfcn.h>
#include <iconv.h>
char* htmlhead(char *lang)
	/*gb2312,utf-8*/
{
	char str[2048];
	sprintf(str,
		"Content-type:text/html;charset=utf-8\r\n\r\n"
		"<html><head>"
		"<meta http-equiv=\"Content-Type\" content=\"text/html;charset=%s\" />"
		"<script language=\"javascript\" type=\"text/javascript\">"
		"function load(){ document.getElementById('post1').submit(); }</script>"
		"</head><body onload='load1()'>",lang,lang);
	return str;
}
char* htmlfrom(char *action,char *xm,char *zjh)
{
	char str[2048];
	sprintf(str,"<form id=\"post1\" name=\"post1\" action=\"%s\" method=\"post\">"
		"<input name=\"zjh\" type=\"hidden\" value=\"%s\" />"
		"<input name=\"xm\" type=\"hidden\" value=\"%s\" />"
		//"<input name=\"poststr\" type=\"submit\" id=\"poststr\" " value=\"submit\" />"
		"</form>",action,zjh,xm);
	return str;
}
char* htmlend(char *xm,char *zjh)
{
	char str[1024];
	sprintf(str,"xm:%s,zjh:%s</body></html>",xm,zjh);
	return str;
}

int main(void)
{

typedef struct userinfo
{
        unsigned short  code;
        unsigned char zjh[18];
        unsigned char yhm[22];
        unsigned char xm[18];
        unsigned char yhlb[6];
        unsigned char dw[42];
        unsigned char email[66];
}USERINFO;
void * libm_handle = NULL;
float (*cosf_method)(char *,char *,char *,USERINFO *);
char *errorInfo;
float result;
libm_handle = dlopen("libthroam.so",RTLD_LAZY);
if(!libm_handle){
        printf("Call libthroam.so  Error:%s.\n",dlerror());
        return 0;
}
cosf_method = dlsym(libm_handle,"thauth_chkticket2userinfo");
errorInfo=dlerror();
if(errorInfo != NULL){
        printf("libthroam Dlsym Error:%s.\n",errorInfo);
        return 0;
}

        int count=0;
        while(FCGI_Accept() >=0){
                char ticket[37],data[120];              //query[50];
                char *hostip,*query;

                hostip=getenv("REMOTE_ADDR");
                if(hostip==NULL || strlen(hostip)==0){
                        /*FCGI_Finish();*/
                        printf("get remoIP error!");
                }else{
                        printf("remoIP:%s </br>",hostip);
                }
                query=getenv("QUERY_STRING");

                if(query == NULL || strlen(query)==0){
                                printf("get getenv QUERY_STRING fail! </br>");
                        }else{
                                printf("getenv QUERY_STRING:%s </br>",query);
                                sscanf(query,"ticket=%s",&ticket);
                                if(strlen(ticket)==0){
                                        printf("get ticket error! </br>");
                                }else{
                                        printf("ticket:%s </br>",ticket);
                                }
                }
                USERINFO ui;
                int retu;
                if(hostip != NULL || ticket != NULL){
                        retu=(*cosf_method)(ticket,"CGYD",hostip,&ui);
                        printf("call thauth_chkticket2string return:%d</br>",retu);
                        printf("return code:0x%04x</br>",ui.code);
                        if(retu <0){printf("Certification fail!</br>");}
                }
                if(ui.code==0)
                {
                //char out[18];
                //g2u(ui.xm,strlen(ui.xm),out,18);
		/*printf("Set-Cookie:cookiexm=%s;cookiezjh=%s;\r\n",ui.xm,ui.zjh);
                printf("Content-type:text/html;charset=gb2312\r\n\r\n");
			printf("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">");
			printf("<html><head>");
			printf("<meta http-equiv=\"Content-Type\" content=\"text/html;charset=gb2312\" />");
		       printf("<script type=\"text/javascript\">");
			printf("window.location.href=");
			printf("\"http://10.220.232.190/Admin/login/ccnu1?xm=%s&zjh=%s\"",ui.xm,ui.zjh);
			printf("</script>\n");
                printf("</head>\r\n<body>");
                printf("<p>xm:%s,zjh:%s</p>",ui.xm,ui.zjh);
                printf("</body>\r\n</html>");
                */
                	printf(htmlhead("gb2312"));
                	printf(htmlfrom("http://10.220.232.190/Admin/login/ccnu1",ui.xm,ui.zjh));
                	printf("系统升级，暂停服务！");
                	printf(htmlend(ui.xm,ui.zjh));
                }else{
                	printf("服务升级，暂停服务！");
                }

        }
        return 0;
dlclose(libm_handle);
}
