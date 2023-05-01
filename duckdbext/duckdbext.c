/* duckdb extension for PHP */

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "zend_exceptions.h"
#include "php_duckdbext.h"
#include "duckdbext_arginfo.h"
#include "duckdb.h"
#include "php_duckdbext_structs.h"
#include "ext/spl/spl_exceptions.h"
#include "zend_interfaces.h"

/* For compatibility with older PHP versions */
#ifndef ZEND_PARSE_PARAMETERS_NONE
#define ZEND_PARSE_PARAMETERS_NONE() \
	ZEND_PARSE_PARAMETERS_START(0, 0) \
	ZEND_PARSE_PARAMETERS_END()
#endif

static zend_class_entry *duckdb_database_ce = NULL;
static zend_class_entry *duckdb_connect_ce = NULL;
static zend_class_entry *duckdb_statementexception_ce = NULL;
static zend_class_entry *duckdb_result_ce = NULL;

static zend_object_handlers duckdb_database_handlers;
static zend_object_handlers duckdb_connection_object_handlers;
static zend_object_handlers duckdb_result_object_handlers;

static zend_object *php_database_object_new(zend_class_entry *class_type)
{
    php_duckdb_db_object *intern;

    /* Allocate memory for it */
    intern = zend_object_alloc(sizeof(php_duckdb_db_object), class_type);

    zend_object_std_init(&intern->zo, class_type);
    object_properties_init(&intern->zo, class_type);

    intern->zo.handlers = &duckdb_database_handlers;

    return &intern->zo;
}

static void php_database_object_free(zend_object *object)
{
    php_duckdb_db_object *intern = php_duckdb_database_from_obj(object);

    if (!intern) {
        return;
    }

    if (intern->db) {
        duckdb_close(&intern->db);
    }

    zend_object_std_dtor(&intern->zo);
}

PHP_METHOD(Database, __construct) {
    char *db_path = NULL;
    size_t db_path_len = 0;

    php_duckdb_db_object *db_obj;
    zval *object = ZEND_THIS;

    db_obj = Z_DUCKDATABASE_P(object);

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(db_path, db_path_len)
    ZEND_PARSE_PARAMETERS_END();

    if (duckdb_open(db_path, &(db_obj->db)) == DuckDBError) {
        zend_throw_exception(zend_ce_exception, "Error during initialization", 0);
        RETURN_THROWS();
    }
}

static zend_object *php_connection_object_new(zend_class_entry *class_type)
{
    // TODO allocate memory for object
    // TODO init object + properties
    // TODO install handlers
}

static void php_connection_object_free(zend_object *object)
{
    // extract object
    // call duckdb_disconnect on connection
    // destroy zend object
}

PHP_METHOD(Connection, __construct) {
    // TODO uncomment class definition in PHP stub
    // TODO get wrap structure from this
    // TODO validate input parameter
    // TODO extract database object from parameter
    // TODO connect to database with duckdb_connect
    // TODO throw an exception if we cannot connect
}

static zend_object *php_result_object_new(zend_class_entry *class_type)
{
    php_duckdb_result_object *intern;

    /* Allocate memory for it */
    intern = zend_object_alloc(sizeof(php_duckdb_result_object), class_type);

    zend_object_std_init(&intern->zo, class_type);
    object_properties_init(&intern->zo, class_type);

    intern->zo.handlers = &duckdb_result_object_handlers;

    return &intern->zo;
}

static void php_result_object_free(zend_object *object)
{
    php_duckdb_result_object *intern = php_duckdb_result_from_obj(object);

    if (!intern) {
        return;
    }

    if (intern->result) {
        duckdb_destroy_result(intern->result);
    }

    efree(intern->result);

    zend_object_std_dtor(&intern->zo);
}

PHP_METHOD(Connection, query)
{
    // TODO allocate memory for the result object
    // TODO extract object from this
    // TODO validate parameters
    // TODO initialize return result object
    // TODO query the database
    // TODO throw an exception if everything go astray
}


PHP_METHOD(Result, __construct)
{

}

void duckdb_value_to_zval(duckdb_result *result, idx_t row, idx_t column, zval *data)
{
    char *val_s;
    val_s = duckdb_value_varchar(result, column, row);

    if (val_s == NULL) {
        ZVAL_NULL(data);
    } else {
        ZVAL_STRING(data, val_s);
        duckdb_free(val_s);
    }
}

PHP_METHOD(Result, toArray)
{
    // TODO extract object
    // TODO register array as a return
    // TODO traverse result set and construct multidimensional array
}

/* {{{ PHP_RINIT_FUNCTION */
PHP_RINIT_FUNCTION(duckdbext)
{
    #if defined(ZTS) && defined(COMPILE_DL_DUCKDB)
        ZEND_TSRMLS_CACHE_UPDATE();
    #endif

    return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION */
PHP_MINFO_FUNCTION(duckdbext)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "duckdb support", "enabled");
    php_info_print_table_end();
}
/* }}} */

/* {{{ PHP_MINIT_FUNCTION */
PHP_MINIT_FUNCTION(duckdbext)
{
    memcpy(&duckdb_database_handlers, &std_object_handlers, sizeof(zend_object_handlers));
    memcpy(&duckdb_connection_object_handlers, &std_object_handlers, sizeof(zend_object_handlers));
    memcpy(&duckdb_result_object_handlers, &std_object_handlers, sizeof(zend_object_handlers));

    zend_class_entry duckdb_database_ce_local;
    INIT_NS_CLASS_ENTRY(duckdb_database_ce_local, "DuckDB","Database", class_Database_methods);
    duckdb_database_ce_local.create_object = php_database_object_new;
    duckdb_database_handlers.offset = XtOffsetOf(php_duckdb_db_object, zo);
    duckdb_database_handlers.clone_obj = NULL;
    duckdb_database_handlers.free_obj = php_database_object_free;
    duckdb_database_ce = zend_register_internal_class(&duckdb_database_ce_local);
    duckdb_database_ce->ce_flags |= ZEND_ACC_NOT_SERIALIZABLE;

    // TODO Add class definition and handlers for DuckDB/Connection

    zend_class_entry duckdb_statementexception_ce_local;
    INIT_NS_CLASS_ENTRY(duckdb_statementexception_ce_local, "DuckDB","StatementException", NULL);
    duckdb_statementexception_ce = zend_register_internal_class_ex(&duckdb_statementexception_ce_local, spl_ce_RuntimeException);

    zend_class_entry duckdb_result_ce_local;
    INIT_NS_CLASS_ENTRY(duckdb_result_ce_local, "DuckDB","Result", class_Result_methods);
    duckdb_result_ce_local.create_object = php_result_object_new;
    duckdb_result_object_handlers.offset = XtOffsetOf(php_duckdb_result_object, zo);
    duckdb_result_object_handlers.clone_obj = NULL;
    duckdb_result_object_handlers.free_obj = php_result_object_free;
    duckdb_result_ce = zend_register_internal_class(&duckdb_result_ce_local);
    duckdb_result_ce->ce_flags |= ZEND_ACC_FINAL|ZEND_ACC_NOT_SERIALIZABLE;

    return SUCCESS;
}
/* }}} */

/* {{{ duckdbext_module_entry */
zend_module_entry duckdbext_module_entry = {
        STANDARD_MODULE_HEADER,
        "duckdbext",			   /* Extension name */
        NULL,				       /* zend_function_entry */
        PHP_MINIT(duckdbext),	   /* PHP_MINIT - Module initialization */
        NULL,	                   /* PHP_MSHUTDOWN - Module shutdown */
        PHP_RINIT(duckdbext),	   /* PHP_RINIT - Request initialization */
        NULL,	                   /* PHP_RSHUTDOWN - Request shutdown */
        PHP_MINFO(duckdbext),	   /* PHP_MINFO - Module info */
        PHP_DUCKDBEXT_VERSION,	   /* Version */
        STANDARD_MODULE_PROPERTIES
};
/* }}} */


#ifdef COMPILE_DL_DUCKDBEXT
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(duckdbext)
#endif