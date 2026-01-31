// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'message.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BMMessage _$BMMessageFromJson(Map<String, dynamic> json) => BMMessage(
      id: (json['message_id'] as num?)?.toInt(),
      threadId: (json['thread_id'] as num?)?.toInt(),
      senderId: (json['sender_id'] as num?)?.toInt(),
      message: json['message'] as String?,
      date: json['date_sent'] as String?,
    );
