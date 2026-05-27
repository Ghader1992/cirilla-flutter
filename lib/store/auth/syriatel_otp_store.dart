import 'package:cirilla/service/helpers/request_helper.dart';
import 'package:cirilla/utils/utils.dart';
import 'package:dio/dio.dart';
import 'package:mobx/mobx.dart';

import 'auth_store.dart';

part 'syriatel_otp_store.g.dart';

class SyriatelOtpStore = SyriatelOtpStoreBase with _$SyriatelOtpStore;

class SyriatelOtpException implements Exception {
  final String message;
  SyriatelOtpException(this.message);

  @override
  String toString() => message;
}

abstract class SyriatelOtpStoreBase with Store {
  late RequestHelper _requestHelper;
  late AuthStore _auth;

  SyriatelOtpStoreBase(RequestHelper requestHelper, AuthStore auth) {
    _requestHelper = requestHelper;
    _auth = auth;
  }

  @observable
  bool _loading = false;

  @computed
  bool get loading => _loading;

  @action
  Future<bool> sendOtp(String phone) async {
    _loading = true;
    try {
      final data = await _requestHelper.syriatelSendOtp(phone: phone);
      if (data['success'] == true) {
        _loading = false;
        return true;
      }
      _loading = false;
      throw SyriatelOtpException(data['message'] ?? 'فشل إرسال الرمز');
    } on DioException catch (e) {
      _loading = false;
      final msg = e.response?.data?['message'] ?? 'خطأ بالشبكة';
      throw SyriatelOtpException(msg);
    }
  }

  @action
  Future<bool> verifyOtp(String phone, String otp) async {
    _loading = true;
    try {
      final data = await _requestHelper.syriatelVerifyOtp(phone: phone, otp: otp);
      avoidPrint('verifyOtp response: $data');
      if (data['success'] == true) {
        // Fix user data format for loginSuccess
        final userData = Map<String, dynamic>.from(data['user'] ?? {});
        userData['ID'] = userData['id']?.toString() ?? '';  // JSON key is 'ID' (uppercase)
        userData['roles'] = userData['roles'] ?? [];
        final fixedData = Map<String, dynamic>.from(data);
        fixedData['user'] = userData;
        await _auth.loginSuccess(fixedData);
        _loading = false;
        return true;
      }
      _loading = false;
      throw SyriatelOtpException(data['message'] ?? 'رمز التحقق غير صحيح');
    } on DioException catch (e) {
      _loading = false;
      avoidPrint('verifyOtp error: ${e.response?.data}');
      final msg = e.response?.data?['message'] ?? 'خطأ بالشبكة';
      throw SyriatelOtpException(msg);
    }
  }
}
