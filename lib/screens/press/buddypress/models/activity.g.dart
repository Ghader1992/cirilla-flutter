// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'activity.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BPActivity _$BPActivityFromJson(Map<String, dynamic> json) => BPActivity(
      id: (json['id'] as num?)?.toInt(),
      content: BPActivity._fromContent(json['content']),
      date: convertToDate(json['date_gmt']),
      favorited: json['favorited'] as bool?,
      type: json['type'] as String?,
      status: json['status'] as String?,
      authorId: (json['user_id'] as num?)?.toInt(),
      title: json['title'] as String?,
      authorAvatar: BPActivity._fromAvatarUrls(json['user_avatar']),
      commentCount: (json['comment_count'] as num?)?.toInt(),
      comments: (json['comments'] as List<dynamic>?)
          ?.map((e) => BPActivity.fromJson(e as Map<String, dynamic>))
          .toList(),
      primaryId: (json['primary_item_id'] as num?)?.toInt(),
      secondaryId: (json['secondary_item_id'] as num?)?.toInt(),
    );
