
/* 
 * Lamb Gateway Platform
 * Copyright (C) 2017 typefo <typefo@qq.com>
 * Update: 2017-07-14
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "queue.h"

lamb_queue_t *lamb_queue_new(int id) {
    lamb_queue_t *self;

    self = malloc(sizeof(lamb_queue_t));
    if (!self) {
        return NULL;
    }

    self->id = id;
    self->len = 0;
    self->head = NULL;
    self->tail = NULL;
    self->next = NULL;
    pthread_mutex_init(&self->lock, NULL);
    
    return self;
}

lamb_node_t *lamb_queue_push(lamb_queue_t *self, void *val) {
    if (!self || !val) {
        return NULL;
    }

    lamb_node_t *node;
    node = (lamb_node_t *)malloc(sizeof(lamb_node_t));

    pthread_mutex_lock(&self->lock);

    if (node) {
        node->val = val;
        node->next = NULL;
        if (self->len) {
            self->tail->next = node;
            self->tail = node;
        } else {
            self->head = self->tail = node;
        }
        ++self->len;
    }

    pthread_mutex_unlock(&self->lock);
    
    return node;
}

lamb_node_t *lamb_queue_pop(lamb_queue_t *self) {
    if (!self || !self->len) {
        return NULL;
    }

    pthread_mutex_lock(&self->lock);
    
    lamb_node_t *node = self->head;

    if (--self->len) {
        self->head = node->next;
    } else {
        self->head = self->tail = NULL;
    }

    pthread_mutex_unlock(&self->lock);
    node->next = NULL;

    return node;
}
