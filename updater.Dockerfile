FROM bitnami/kubectl:latest
RUN mkdir -p /tmp/plugin
COPY * /tmp/plugin/