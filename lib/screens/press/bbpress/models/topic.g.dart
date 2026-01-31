// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'topic.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BBPTopic _$BBPTopicFromJson(Map<String, dynamic> json) => BBPTopic(
      id: (json['id'] as num?)?.toInt(),
      title: json['title'] as String?,
      replyCount: ConvertData.stringToInt(json['reply_count']),
      authorName: json['author_name'] as String?,
      authorAvatar: json['author_avatar'] as String?,
      date: json['post_date'] as String?,
      forumId: (json['forum_id'] as num?)?.toInt(),
      forumTitle: json['forum_title'] as String?,
    );
