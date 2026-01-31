// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'forum.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BBPForum _$BBPForumFromJson(Map<String, dynamic> json) => BBPForum(
      id: (json['id'] as num?)?.toInt(),
      title: unescape(json['title']),
      parent: (json['parent'] as num?)?.toInt(),
      topicCount: ConvertData.stringToInt(json['topic_count']),
      replyCount: ConvertData.stringToInt(json['reply_count']),
      content: json['content'] as String?,
      type: json['type'] as String?,
    );
