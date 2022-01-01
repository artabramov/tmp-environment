FROM docker.elastic.co/beats/filebeat:7.6.2
#RUN chmod go-w /usr/share/filebeat/filebeat.yml
COPY --chown=root:filebeat ./filebeat.yml /usr/share/filebeat/filebeat.yml
#RUN echo "fuck" > /usr/share/filebeat/filebeat.yml
#ENTRYPOINT ["filebeat", "-e", "--strict.perms=false"]

