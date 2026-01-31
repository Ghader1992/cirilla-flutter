// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'message.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BPMessage _$BPMessageFromJson(Map<String, dynamic> json) => BPMessage(
      id: (json['id'] as num?)?.toInt(),
      senderId: (json['sender_id'] as num?)?.toInt(),
      title: BPMessage._fromText(json['subject']),
      message: BPMessage._fromText(json['message']),
      date: convertToDate(json['date_sent']),
    );
