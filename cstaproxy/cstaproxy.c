/* This program is part of the XP framework
 *
 * $Id$
 */

#include <stdlib.h>
#include <stdio.h>    	/* for printf */
#include <stdarg.h>		/* va_* */

/* Socket */
#include <sys/types.h>
#include <sys/socket.h>

/* Regex */
#include <regex.h>

#include "cstaproxy.h"
#include "csta_error.h"
#include "csta_network.h"

int regex_match (char *haystack, char *needle) {
	regex_t *rctx;
	regmatch_t *pmatch[2];
	int result, retcode;
	
	result= FALSE;
	rctx= (regex_t *)malloc (sizeof (regex_t));
	
	/* Compile the regular expression */
	if (0 != (retcode= regcomp (rctx, needle, REG_EXTENDED|REG_ICASE|REG_NEWLINE))) {
		char *errbuf;
		
		errbuf= malloc (sizeof (char)*256);
		regerror (retcode, rctx, errbuf, 256);
		print_error (__FILE__, __LINE__, "%s", errbuf);
		free (errbuf);
		regfree (rctx);
		
		return FALSE;
	}
	
	/* Allocate memory for matches */
	*pmatch= (regmatch_t*)malloc (sizeof (regmatch_t) * 10);
	
	/* Execute regular expression */
	if (0 != (retcode= regexec (rctx, haystack, sizeof (pmatch), *pmatch, 0))) {
		return FALSE;
	}
	
	return TRUE;
}

int main(int argc, char **argv) {
	int hListen;
	
	hListen= create_listening_socket (MYADDR, MYPORT);
	
	return 0;
}
