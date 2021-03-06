
/* 
 * Lamb Gateway Platform
 * Copyright (C) 2017 typefo <typefo@qq.com>
 */

#include <stdlib.h>
#include <string.h>
#include "channel.h"

int lamb_get_channels(lamb_db_t *db, int acc, lamb_list_t *channels) {
    int rows;
    char *column;
    char sql[256];
    PGresult *res = NULL;

    channels->len = 0;
    column = "id, acc, weight, operator";
    snprintf(sql, sizeof(sql), "SELECT %s FROM channels WHERE acc = %d ORDER BY weight ASC",
             column, acc);
    res = PQexec(db->conn, sql);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        PQclear(res);
        return -1;
    }

    rows = PQntuples(res);

    for (int i = 0; (i < rows); i++) {
        lamb_channel_t *c = NULL;
        c = (lamb_channel_t *)calloc(1, sizeof(lamb_channel_t));
        if (c != NULL) {
            c->id = atoi(PQgetvalue(res, i, 0));
            c->acc = atoi(PQgetvalue(res, i, 1));
            c->weight = atoi(PQgetvalue(res, i, 2));
            c->operator = atoi(PQgetvalue(res, i, 3));
            lamb_list_rpush(channels, lamb_node_new(c));
        }
    }

    PQclear(res);

    return 0;
}
