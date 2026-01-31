// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'chat.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BMChat _$BMChatFromJson(Map<String, dynamic> json) => BMChat(
      id: (json['thread_id'] as num?)?.toInt(),
      title: json['title'] as String?,
      participants: BMChat._fromUser(json['participants']),
      participantsCount: (json['participantsCount'] as num?)?.toInt(),
      messages: BMChat._fromMessage(json['messages']),
      unread: (json['unread'] as num?)?.toInt(),
    );
