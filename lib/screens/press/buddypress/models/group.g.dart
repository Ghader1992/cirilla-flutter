// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'group.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BPGroup _$BPGroupFromJson(Map<String, dynamic> json) => BPGroup(
      id: (json['id'] as num?)?.toInt(),
      creatorId: (json['creator_id'] as num?)?.toInt(),
      parentId: (json['parent_id'] as num?)?.toInt(),
      name: json['name'] as String?,
      status: json['status'] as String?,
      createdAt: convertToDate(json['date_created_gmt']),
      createdSince: json['created_since'] as String?,
      avatar: BPGroup._fromAvatarUrls(json['avatar_urls']),
      lastActivity: convertToDate(json['last_activity_gmt']),
      memberCount: ConvertData.stringToInt(json['total_member_count']),
      description: BPGroup._fromDescription(json['description']),
    );
