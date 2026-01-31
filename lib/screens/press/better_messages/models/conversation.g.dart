// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'conversation.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BMConversation _$BMConversationFromJson(Map<String, dynamic> json) =>
    BMConversation(
      id: (json['thread_id'] as num?)?.toInt(),
      title: json['title'] as String?,
      lastTime: json['lastTime'] as String?,
      participants: BMConversation._fromUser(json['participants']),
      participantsCount: (json['participantsCount'] as num?)?.toInt(),
      lastMessage: BMConversation._fromMessage(json['lastMessage']),
      unread: (json['unread'] as num?)?.toInt(),
    );
